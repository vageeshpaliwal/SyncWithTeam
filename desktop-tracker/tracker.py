import sys
import os
import json
import time
import base64
import threading
import subprocess
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs
import urllib.request
import urllib.error

PORT = 49152
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
CAPTURE_SCRIPT = os.path.join(SCRIPT_DIR, "capture.ps1")
CONFIG_FILE = os.path.join(SCRIPT_DIR, "config.json")

tracker_state = {
    "running": False,
    "user_id": 1,
    "project_id": None,
    "task_id": None,
    "api_url": "http://localhost/syncWithteam/public/api/v1/attendance/screenshot",
    "active_session_url": "http://localhost/syncWithteam/public/api/v1/attendance/active-session",
    "interval_seconds": 120,
    "last_captured_at": None,
    "captures_count": 0,
    "last_status_message": "Idle (Waiting for timer to start)"
}

capture_timer = None

def save_config():
    try:
        cfg_data = {
            "userId": tracker_state["user_id"],
            "apiUrl": tracker_state["api_url"],
            "activeSessionUrl": tracker_state["active_session_url"],
            "intervalSeconds": tracker_state["interval_seconds"]
        }
        with open(CONFIG_FILE, "w", encoding="utf-8") as f:
            json.dump(cfg_data, f, indent=2)
        print("[Config] Settings saved to config.json")
    except Exception as e:
        print(f"[Config] Error writing config: {e}")

if os.path.exists(CONFIG_FILE):
    try:
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            cfg = json.load(f)
            if "userId" in cfg: tracker_state["user_id"] = cfg["userId"]
            if "apiUrl" in cfg: tracker_state["api_url"] = cfg["apiUrl"]
            if "activeSessionUrl" in cfg: tracker_state["active_session_url"] = cfg["activeSessionUrl"]
            if "intervalSeconds" in cfg: tracker_state["interval_seconds"] = cfg["intervalSeconds"]
            print(f"[Config] Loaded settings from config.json (User #{tracker_state['user_id']})")
    except Exception as e:
        print(f"[Config] Error: {e}")

def capture_desktop_screen():
    cmd = ["powershell.exe", "-NoProfile", "-ExecutionPolicy", "Bypass", "-File", CAPTURE_SCRIPT]
    res = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, check=True)
    return res.stdout.strip()

def upload_screenshot(base64_data):
    payload = json.dumps({
        "user_id": tracker_state["user_id"],
        "project_id": tracker_state["project_id"],
        "task_id": tracker_state["task_id"],
        "image_data": "data:image/jpeg;base64," + base64_data
    }).encode("utf-8")

    req = urllib.request.Request(
        tracker_state["api_url"],
        data=payload,
        headers={
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-User-Id": str(tracker_state["user_id"] or "")
        }
    )
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode("utf-8"))

def perform_capture_cycle():
    t_str = time.strftime("%H:%M:%S")
    try:
        print(f"[{t_str}] 📸 Capturing full laptop screen...")
        b64 = capture_desktop_screen()
        print(f"[{t_str}] ⬆️ Uploading to SyncWithTeam backend (User #{tracker_state['user_id']})...")
        res = upload_screenshot(b64)
        tracker_state["last_captured_at"] = time.strftime("%Y-%m-%d %H:%M:%S")
        tracker_state["captures_count"] += 1
        tracker_state["last_status_message"] = f"Last capture successful at {t_str} (Total: {tracker_state['captures_count']})"
        print(f"[{t_str}] ✅ Screenshot recorded successfully! (Total: {tracker_state['captures_count']})")
        return res
    except Exception as e:
        tracker_state["last_status_message"] = f"Capture error at {t_str}: {e}"
        print(f"[{t_str}] ❌ Capture/Upload Error: {e}")
        raise e

def capture_loop():
    while tracker_state["running"]:
        for _ in range(tracker_state["interval_seconds"]):
            if not tracker_state["running"]:
                break
            time.sleep(1)
        if tracker_state["running"]:
            perform_capture_cycle()

def start_tracking(options=None):
    if options:
        if "userId" in options or "user_id" in options:
            tracker_state["user_id"] = options.get("userId") or options.get("user_id")
        if "projectId" in options or "project_id" in options:
            tracker_state["project_id"] = options.get("projectId") or options.get("project_id")
        if "taskId" in options or "task_id" in options:
            tracker_state["task_id"] = options.get("taskId") or options.get("task_id")
        if "apiUrl" in options:
            tracker_state["api_url"] = options.get("apiUrl")
        if "activeSessionUrl" in options:
            tracker_state["active_session_url"] = options.get("activeSessionUrl")
        if "intervalSeconds" in options or "interval_seconds" in options:
            tracker_state["interval_seconds"] = int(options.get("intervalSeconds") or options.get("interval_seconds"))

    save_config()

    tracker_state["running"] = True
    print(f"\n🟢 [Tracker] Tracking STARTED for User #{tracker_state['user_id']} (Interval: {tracker_state['interval_seconds']}s)")
    
    # Trigger immediate capture in a separate thread
    threading.Thread(target=perform_capture_cycle, daemon=True).start()

    th = threading.Thread(target=capture_loop, daemon=True)
    th.start()

def stop_tracking():
    tracker_state["running"] = False
    print(f"\n🔴 [Tracker] Tracking STOPPED. Total captured: {tracker_state['captures_count']}")
    tracker_state["last_status_message"] = "Idle (Timer stopped)"

class RequestHandler(BaseHTTPRequestHandler):
    def _send_cors_headers(self):
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type, X-User-Id, Accept, Authorization")

    def do_OPTIONS(self):
        self.send_response(204)
        self._send_cors_headers()
        self.end_headers()

    def do_GET(self):
        parsed = urlparse(self.path)
        if parsed.path == "/status":
            self.send_response(200)
            self._send_cors_headers()
            self.send_header("Content-Type", "application/json")
            self.end_headers()
            self.wfile.write(json.dumps({"ok": True, "state": tracker_state}).encode("utf-8"))
        else:
            self.send_response(404)
            self._send_cors_headers()
            self.end_headers()

    def do_POST(self):
        parsed = urlparse(self.path)
        content_length = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(content_length).decode("utf-8") if content_length > 0 else "{}"
        try:
            params = json.loads(body) if body else {}
        except:
            params = {}

        if parsed.path == "/start":
            start_tracking(params)
            self.send_response(200)
            self._send_cors_headers()
            self.send_header("Content-Type", "application/json")
            self.end_headers()
            self.wfile.write(json.dumps({"success": True, "message": "Tracking started", "state": tracker_state}).encode("utf-8"))
        elif parsed.path == "/stop":
            stop_tracking()
            self.send_response(200)
            self._send_cors_headers()
            self.send_header("Content-Type", "application/json")
            self.end_headers()
            self.wfile.write(json.dumps({"success": True, "message": "Tracking stopped", "state": tracker_state}).encode("utf-8"))
        elif parsed.path == "/capture-now":
            try:
                res = perform_capture_cycle()
                self.send_response(200)
                self._send_cors_headers()
                self.send_header("Content-Type", "application/json")
                self.end_headers()
                self.wfile.write(json.dumps({"success": True, "message": "Captured", "data": res, "state": tracker_state}).encode("utf-8"))
            except Exception as e:
                self.send_response(500)
                self._send_cors_headers()
                self.send_header("Content-Type", "application/json")
                self.end_headers()
                self.wfile.write(json.dumps({"success": False, "message": str(e)}).encode("utf-8"))
        else:
            self.send_response(404)
            self._send_cors_headers()
            self.end_headers()

def run_server():
    server = HTTPServer(("127.0.0.1", PORT), RequestHandler)
    print("===============================================================")
    print("  🚀 SyncWithTeam - Desktop Companion Tracker Agent (Python)")
    print("===============================================================")
    print(f"  🌐 Local Bridge Running at: http://127.0.0.1:{PORT}")
    print("  🔒 Capture Mode: 100% Silent (Zero Browser Popups)")
    print("  🖥️ Screen Scope: Entire Desktop & Laptop Windows")
    print("  ⏱️ Default Interval: Every 2 minutes (120 seconds)")
    print("===============================================================\n")
    server.serve_forever()

if __name__ == "__main__":
    run_server()
