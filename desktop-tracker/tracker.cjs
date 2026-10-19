const http = require('http');
const { execFile } = require('child_process');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = process.env.PORT || 49152;
const CAPTURE_SCRIPT = path.join(__dirname, 'capture.ps1');
const CONFIG_FILE = path.join(__dirname, 'config.json');

// State
let trackerState = {
    running: false,
    userId: 1,
    projectId: null,
    taskId: null,
    apiUrl: 'http://127.0.0.1:8000/api/v1/attendance/screenshot',
    activeSessionUrl: 'http://127.0.0.1:8000/api/v1/attendance/active-session',
    minIntervalSeconds: 120, // Minimum 2 minutes gap
    maxIntervalSeconds: 270, // Up to 4.5 minutes
    lastCapturedAt: null,
    capturesCount: 0,
    lastStatusMessage: 'Idle (Waiting for timer to start)'
};

let captureTimeoutId = null;
let pollIntervalId = null;

// Save current settings to config.json (UTF-8 without BOM)
function saveConfig() {
    try {
        const configData = {
            userId: trackerState.userId,
            apiUrl: trackerState.apiUrl,
            activeSessionUrl: trackerState.activeSessionUrl,
            minIntervalSeconds: trackerState.minIntervalSeconds,
            maxIntervalSeconds: trackerState.maxIntervalSeconds
        };
        fs.writeFileSync(CONFIG_FILE, JSON.stringify(configData, null, 2), 'utf8');
        console.log('[Config] Settings saved to config.json');
    } catch (e) {
        console.error('[Config] Error writing config.json:', e.message);
    }
}

// Read config.json if present
if (fs.existsSync(CONFIG_FILE)) {
    try {
        const rawContent = fs.readFileSync(CONFIG_FILE, 'utf8').replace(/^\uFEFF/, '');
        const config = JSON.parse(rawContent);
        if (config.userId) trackerState.userId = config.userId;
        if (config.apiUrl) trackerState.apiUrl = config.apiUrl;
        if (config.activeSessionUrl) trackerState.activeSessionUrl = config.activeSessionUrl;
        if (config.minIntervalSeconds) trackerState.minIntervalSeconds = config.minIntervalSeconds;
        if (config.maxIntervalSeconds) trackerState.maxIntervalSeconds = config.maxIntervalSeconds;
        console.log('[Config] Loaded settings from config.json (User #' + trackerState.userId + ')');
    } catch (e) {
        console.error('[Config] Error reading config.json:', e.message);
    }
}

// Function to capture full desktop screenshot via native PowerShell .NET + Win32 DPI Awareness
function captureDesktopScreen() {
    return new Promise((resolve, reject) => {
        execFile('powershell.exe', [
            '-NoProfile',
            '-ExecutionPolicy', 'Bypass',
            '-File', CAPTURE_SCRIPT
        ], { maxBuffer: 50 * 1024 * 1024 }, (error, stdout, stderr) => {
            if (error) {
                return reject(error);
            }
            const base64Data = stdout.trim();
            if (!base64Data || base64Data.length < 100) {
                return reject(new Error('Empty screenshot output from PowerShell script'));
            }
            resolve(base64Data);
        });
    });
}

function makeUploadRequest(targetUrlStr, payload, userId) {
    return new Promise((resolve, reject) => {
        const targetUrl = new URL(targetUrlStr);
        const isHttps = targetUrl.protocol === 'https:';
        const client = isHttps ? require('https') : require('http');

        const req = client.request(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(payload),
                'Accept': 'application/json',
                'X-User-Id': userId || ''
            },
            timeout: 30000
        }, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(body);
                    if (res.statusCode >= 200 && res.statusCode < 300 && parsed.success !== false) {
                        resolve(parsed);
                    } else {
                        reject(new Error(parsed.message || `HTTP ${res.statusCode}: ${body.substring(0, 100)}`));
                    }
                } catch (e) {
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve({ success: true, body });
                    } else {
                        reject(new Error(`HTTP ${res.statusCode}: ${body.substring(0, 100)}`));
                    }
                }
            });
        });

        req.on('error', (err) => reject(err));
        req.write(payload);
        req.end();
    });
}

// Function to upload captured screenshot to Laravel API with automatic fallback
async function uploadScreenshot(base64Image) {
    const payload = JSON.stringify({
        user_id: trackerState.userId,
        project_id: trackerState.projectId,
        task_id: trackerState.taskId,
        image_data: 'data:image/jpeg;base64,' + base64Image
    });

    try {
        return await makeUploadRequest(trackerState.apiUrl, payload, trackerState.userId);
    } catch (err) {
        // If connection failed on default URL (e.g. port 80 or localhost), try fallback to 127.0.0.1:8000
        if (err.code === 'ECONNREFUSED' && !trackerState.apiUrl.includes(':8000')) {
            const fallbackUrl = 'http://127.0.0.1:8000/api/v1/attendance/screenshot';
            console.log(`[Upload] Primary URL failed (${err.message}). Retrying fallback: ${fallbackUrl}`);
            trackerState.apiUrl = fallbackUrl;
            return await makeUploadRequest(fallbackUrl, payload, trackerState.userId);
        }
        throw err;
    }
}

// Perform a single capture and upload cycle
async function performCaptureCycle() {
    const timestamp = new Date().toLocaleTimeString();
    try {
        console.log(`[${timestamp}] 📸 Capturing full laptop screen...`);
        const base64Image = await captureDesktopScreen();
        console.log(`[${timestamp}] ⬆️ Uploading to SyncWithTeam backend (User #${trackerState.userId})...`);
        const result = await uploadScreenshot(base64Image);
        trackerState.lastCapturedAt = new Date().toISOString();
        trackerState.capturesCount++;
        trackerState.lastStatusMessage = `Last capture successful at ${timestamp} (Total: ${trackerState.capturesCount})`;
        console.log(`[${timestamp}] ✅ Screenshot recorded successfully! (Total: ${trackerState.capturesCount})`);
        return result;
    } catch (err) {
        const errMsg = err.message || err.code || String(err);
        trackerState.lastStatusMessage = `Capture error at ${timestamp}: ${errMsg}`;
        console.error(`[${timestamp}] ❌ Capture/Upload Error:`, errMsg);
        throw err;
    }
}

// Schedule next capture with a randomized delay (minimum 2 minutes guaranteed)
function scheduleNextCapture() {
    if (captureTimeoutId) {
        clearTimeout(captureTimeoutId);
        captureTimeoutId = null;
    }

    if (!trackerState.running) return;

    // Minimum gap: 120 seconds (2 minutes). Random range: 120s to 270s (2m to 4.5m)
    const minSecs = Math.max(120, trackerState.minIntervalSeconds || 120);
    const maxSecs = Math.max(minSecs + 60, trackerState.maxIntervalSeconds || 270);
    const nextGapSecs = Math.floor(Math.random() * (maxSecs - minSecs + 1)) + minSecs;

    console.log(`[Timer] Next random screenshot scheduled in ${nextGapSecs}s (${(nextGapSecs / 60).toFixed(1)} mins, min gap: 2m)`);

    captureTimeoutId = setTimeout(async () => {
        if (trackerState.running) {
            try {
                await performCaptureCycle();
            } catch (e) {}
            scheduleNextCapture();
        }
    }, nextGapSecs * 1000);
}

// Start or update tracking session
function startTracking(options = {}) {
    const newUserId = options.userId || options.user_id;
    if (newUserId) trackerState.userId = newUserId;
    if (options.projectId !== undefined) trackerState.projectId = options.projectId;
    if (options.task_id !== undefined || options.taskId !== undefined) {
        trackerState.taskId = options.taskId !== undefined ? options.taskId : options.task_id;
    }
    if (options.apiUrl) trackerState.apiUrl = options.apiUrl;
    if (options.activeSessionUrl) trackerState.activeSessionUrl = options.activeSessionUrl;
    if (options.minIntervalSeconds || options.interval_seconds || options.intervalSeconds) {
        const givenMin = options.minIntervalSeconds || options.interval_seconds || options.intervalSeconds;
        trackerState.minIntervalSeconds = Math.max(120, givenMin);
    }

    saveConfig();

    trackerState.running = true;
    console.log(`\n🟢 [Tracker] Tracking STARTED/UPDATED for User #${trackerState.userId} (Random gap: >= 2 mins)`);

    // Perform immediate screenshot capture after 1 second, then schedule random captures
    setTimeout(async () => {
        if (trackerState.running) {
            try {
                await performCaptureCycle();
            } catch (e) {}
            scheduleNextCapture();
        }
    }, 1000);
}

// Stop recurring tracking
function stopTracking() {
    trackerState.running = false;
    if (captureTimeoutId) {
        clearTimeout(captureTimeoutId);
        captureTimeoutId = null;
    }
    console.log(`\n🔴 [Tracker] Tracking STOPPED. (Total screenshots recorded: ${trackerState.capturesCount})`);
    trackerState.lastStatusMessage = 'Idle (Timer stopped)';
}

// Check active session on server (Auto-sync with web dashboard)
function pollActiveSession() {
    if (!trackerState.userId || !trackerState.activeSessionUrl) return;

    try {
        const checkUrl = new URL(trackerState.activeSessionUrl);
        checkUrl.searchParams.set('user_id', trackerState.userId);

        const isHttps = checkUrl.protocol === 'https:';
        const client = isHttps ? require('https') : require('http');

        const req = client.get(checkUrl, {
            headers: { 'Accept': 'application/json', 'X-User-Id': trackerState.userId },
            timeout: 10000
        }, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    const data = JSON.parse(body);
                    if (data.active === true) {
                        if (!trackerState.running) {
                            console.log(`[Auto-Sync] Active timer detected on web portal for User #${trackerState.userId}. Auto-starting capture...`);
                            startTracking({
                                userId: data.user_id,
                                projectId: data.project_id,
                                taskId: data.task_id
                            });
                        }
                    } else {
                        if (trackerState.running) {
                            console.log(`[Auto-Sync] Timer was stopped on web portal. Auto-stopping capture...`);
                            stopTracking();
                        }
                    }
                } catch (e) {}
            });
        });
        req.on('error', () => {});
    } catch (e) {}
}

// Start auto-sync polling every 20 seconds
pollIntervalId = setInterval(pollActiveSession, 20000);

// HTTP Server for Local Web Bridge
const server = http.createServer((req, res) => {
    // CORS Headers for seamless local communication from web browser
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-User-Id, Accept, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const parsedUrl = url.parse(req.url, true);

    if (parsedUrl.pathname === '/status') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            ok: true,
            version: '2.2.0',
            state: trackerState
        }));
        return;
    }

    if (parsedUrl.pathname === '/start' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const params = body ? JSON.parse(body) : {};
                startTracking(params);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, message: 'Tracking started & immediate screenshot triggered (Random min 2m gap)', state: trackerState }));
            } catch (e) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, message: e.message }));
            }
        });
        return;
    }

    if (parsedUrl.pathname === '/stop' && req.method === 'POST') {
        stopTracking();
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: true, message: 'Tracking stopped', state: trackerState }));
        return;
    }

    if (parsedUrl.pathname === '/capture-now' && req.method === 'POST') {
        performCaptureCycle().then((result) => {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: true, message: 'Capture executed successfully', data: result, state: trackerState }));
        }).catch(err => {
            res.writeHead(500, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: false, message: err.message || err.code || String(err) }));
        });
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Endpoint not found' }));
});

server.listen(PORT, '127.0.0.1', () => {
    console.log('===============================================================');
    console.log('  🚀 SyncWithTeam - Desktop Companion Tracker Agent v2.2.0');
    console.log('===============================================================');
    console.log(`  🌐 Local Bridge Running at: http://127.0.0.1:${PORT}`);
    console.log('  🔒 Capture Mode: 100% Silent (Zero Browser Popups)');
    console.log('  🖥️ Screen Scope: Entire Desktop & Laptop Windows');
    console.log('  ⏱️ Random Interval: Randomized captures (At least 2 minutes gap)');
    console.log('===============================================================\n');
    console.log('Ready! Start timer on web dashboard or configure user_id in config.json.\n');

    // Run initial auto-sync check
    if (trackerState.userId) {
        pollActiveSession();
    }
});
