@echo off
title SyncWithTeam - Remove Auto-Start
color 0C
cls
echo Removing tracker from Windows Startup...
del /F /Q "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\SyncWithTeam-Tracker.lnk" 2>nul
echo [OK] Startup shortcut removed.
pause
