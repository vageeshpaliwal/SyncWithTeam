const http = require('http');
const { execFile, exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = process.env.PORT || 49152;
const CAPTURE_SCRIPT = path.join(__dirname, 'capture.ps1');

// State
let trackerState = {
    running: false,
    userId: null,
    projectId: null,
    taskId: null,
    apiUrl: 'http://localhost/syncWithteam/public/api/v1/attendance/screenshot',
    activeSessionUrl: 'http://localhost/syncWithteam/public/api/v1/attendance/active-session',
    intervalSeconds: 120, // 2 minutes
    lastCapturedAt: null,
    capturesCount: 0,
    lastStatusMessage: 'Idle (Waiting for timer to start)'
};

let captureIntervalId = null;
let pollIntervalId = null;

// Read config.json if present
const configPath = path.join(__dirname, 'config.json');
if (fs.existsSync(configPath)) {
    try {
        const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        if (config.userId) trackerState.userId = config.userId;
        if (config.apiUrl) trackerState.apiUrl = config.apiUrl;
        if (config.activeSessionUrl) trackerState.activeSessionUrl = config.activeSessionUrl;
        if (config.intervalSeconds) trackerState.intervalSeconds = config.intervalSeconds;
        console.log('[Config] Loaded settings from config.json');
    } catch (e) {
        console.error('[Config] Error reading config.json:', e.message);
    }
}

// Function to capture full desktop screenshot via native PowerShell .NET
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
                return reject(new Error('Empty screenshot output from PowerShell'));
            }
            resolve(base64Data);
        });
    });
}

// Function to upload captured screenshot to Laravel API
function uploadScreenshot(base64Image) {
    return new Promise((resolve, reject) => {
        const targetUrl = new URL(trackerState.apiUrl);
        const isHttps = targetUrl.protocol === 'https:';
        const client = isHttps ? require('https') : require('http');

        const payload = JSON.stringify({
            user_id: trackerState.userId,
            project_id: trackerState.projectId,
            task_id: trackerState.taskId,
            image_data: 'data:image/jpeg;base64,' + base64Image
        });

        const req = client.request(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(payload),
                'Accept': 'application/json',
                'X-User-Id': trackerState.userId || ''
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

        req.on('error', reject);
        req.write(payload);
        req.end();
    });
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
    } catch (err) {
        trackerState.lastStatusMessage = `Capture error at ${timestamp}: ${err.message}`;
        console.error(`[${timestamp}] ❌ Capture/Upload Error:`, err.message);
    }
}

// Start recurring tracking
function startTracking(options = {}) {
    if (options.userId) trackerState.userId = options.userId;
    if (options.projectId !== undefined) trackerState.projectId = options.projectId;
    if (options.taskId !== undefined) trackerState.taskId = options.taskId;
    if (options.apiUrl) trackerState.apiUrl = options.apiUrl;
    if (options.intervalSeconds) trackerState.intervalSeconds = options.intervalSeconds;

    if (trackerState.running && captureIntervalId) {
        console.log(`[Tracker] Already running for User #${trackerState.userId}. Updated session details.`);
        return;
    }

    trackerState.running = true;
    console.log(`\n🟢 [Tracker] Tracking STARTED for User #${trackerState.userId} (Interval: ${trackerState.intervalSeconds}s = ${trackerState.intervalSeconds/60} mins)`);

    // Perform immediate capture after 4 seconds
    setTimeout(() => {
        if (trackerState.running) {
            performCaptureCycle();
        }
    }, 4000);

    // Schedule recurring interval
    if (captureIntervalId) clearInterval(captureIntervalId);
    captureIntervalId = setInterval(() => {
        if (trackerState.running) {
            performCaptureCycle();
        }
    }, trackerState.intervalSeconds * 1000);
}

// Stop recurring tracking
function stopTracking() {
    trackerState.running = false;
    if (captureIntervalId) {
        clearInterval(captureIntervalId);
        captureIntervalId = null;
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
                                taskId: data.task_id,
                                intervalSeconds: data.interval_seconds || 120
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
            version: '2.0.0',
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
                res.end(JSON.stringify({ success: true, message: 'Tracking started', state: trackerState }));
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
        performCaptureCycle().then(() => {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: true, message: 'Capture executed', state: trackerState }));
        }).catch(err => {
            res.writeHead(500, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: false, message: err.message }));
        });
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Endpoint not found' }));
});

server.listen(PORT, '127.0.0.1', () => {
    console.log('===============================================================');
    console.log('  🚀 SyncWithTeam - Desktop Companion Tracker Agent');
    console.log('===============================================================');
    console.log(`  🌐 Local Bridge Running at: http://127.0.0.1:${PORT}`);
    console.log('  🔒 Capture Mode: 100% Silent (Zero Browser Popups)');
    console.log('  🖥️ Screen Scope: Entire Desktop & Laptop Windows');
    console.log('  ⏱️ Default Interval: Every 2 minutes (120 seconds)');
    console.log('===============================================================\n');
    console.log('Ready! Start timer on web dashboard or configure user_id in config.json.\n');

    // Run initial auto-sync check
    if (trackerState.userId) {
        pollActiveSession();
    }
});
