@echo off
title SyncWithTeam - Enable Auto-Start
color 0A
cls
echo =====================================================================
echo       SYNCWITHTEAM - ADD TO WINDOWS AUTO-START
echo =====================================================================
echo.
echo Adding tracker shortcut to your Windows Startup folder...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut(\"$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\SyncWithTeam-Tracker.lnk\"); $s.TargetPath = \"$PSScriptRoot\Start-Tracker.bat\"; $s.WorkingDirectory = \"$PSScriptRoot\"; $s.WindowStyle = 7; $s.Save(); Write-Host '[OK] Startup shortcut created successfully!'"

echo.
echo =====================================================================
echo  SUCCESS! Tracker will now start minimized on Windows startup.
echo =====================================================================
echo.
echo Starting tracker now...
start "" "%~dp0Start-Tracker.bat"
timeout /t 3 >nul
