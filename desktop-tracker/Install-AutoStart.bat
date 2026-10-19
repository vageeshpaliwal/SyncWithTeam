@echo off
title SyncWithTeam - Enable Auto-Start & Protocol
color 0A
cls
echo =====================================================================
echo       SYNCWITHTEAM - ADD TO WINDOWS AUTO-START & REGISTRY
echo =====================================================================
echo.
echo 1. Unblocking internet downloaded files...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-ChildItem -Path '%~dp0*' -Recurse | Unblock-File" >nul 2>&1
echo [OK] Files unblocked.

echo.
echo 2. Adding tracker shortcut to your Windows Startup folder...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut(\"$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\SyncWithTeam-Tracker.lnk\"); $s.TargetPath = \"$PSScriptRoot\Start-Tracker.bat\"; $s.WorkingDirectory = \"$PSScriptRoot\"; $s.WindowStyle = 7; $s.Save(); Write-Host '[OK] Startup shortcut created successfully!'"

echo.
echo 3. Registering browser protocol...
set "TRACKER_BAT=%~dp0Start-Tracker.bat"
reg add "HKCU\Software\Classes\syncwithteam" /ve /d "URL:SyncWithTeam Protocol" /f >nul
reg add "HKCU\Software\Classes\syncwithteam" /v "URL Protocol" /d "" /f >nul
reg add "HKCU\Software\Classes\syncwithteam\shell\open\command" /ve /d "\"%TRACKER_BAT%\" %%1" /f >nul
echo [OK] Browser protocol registered!

echo.
echo =====================================================================
echo  SUCCESS! Tracker installed and enabled on Windows startup.
echo =====================================================================
echo.
echo Starting tracker now...
start "" "%TRACKER_BAT%"
timeout /t 3 >nul
