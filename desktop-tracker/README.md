# SyncWithTeam - Native Desktop Companion Tracker

A silent, lightweight background companion agent for **SyncWithTeam** that takes screenshots of the entire laptop screen (including all desktop windows, IDEs, browser, and apps) every 2 minutes while the work timer is active, with **0 browser popups or permissions**.

---

## 🌟 Key Features

- **Zero Browser Popups**: Runs natively in the background without annoying browser screen share prompts.
- **Full Laptop Desktop Capture**: Captures whatever apps the employee is working on (VS Code, Excel, Slack, Chrome, Terminals, etc.).
- **Automatic 2-Minute Intervals**: Automatically takes and uploads screenshots every 2 minutes while the user is clocked in.
- **Instant Local Sync**: Communicates with the web portal over a lightweight localhost bridge (`http://127.0.0.1:49152`).
- **Auto-Sync Standalone Mode**: Automatically detects when a user starts or stops their timer from any browser or device.
- **30-Day Auto Retention**: Automatically deletes screenshots older than 30 days / 1 month.

---

## 🚀 Quick Start (Windows 1-Click)

1. Open the `desktop-tracker/` folder.
2. Double-click **`Start-Tracker.bat`**.
3. That's it! The tracker is now active on your laptop.
4. Go to your SyncWithTeam web dashboard (`Time Logs & Tracker`), and click **Start**.
5. Screenshots will automatically start capturing and appear in your **Time Logs > Screenshots & Activity** tab day-wise!

---

## ⚙️ Manual / Console Execution

### Node.js (Recommended)
```bash
cd desktop-tracker
node tracker.cjs
```

### Python Alternative
```bash
cd desktop-tracker
python tracker.py
```

---

## 📁 File Structure

- `Start-Tracker.bat` - 1-Click Windows launcher.
- `tracker.cjs` - Node.js background HTTP bridge & auto-capture daemon.
- `tracker.py` - Python background HTTP bridge alternative.
- `capture.ps1` - Native Windows .NET screenshot capture engine (zero external dependencies).
- `config.json` - Custom settings (API URL, User ID, custom interval).
