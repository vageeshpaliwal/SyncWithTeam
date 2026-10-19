@echo off
title SyncWithTeam - Register Protocol
cls
echo =====================================================================
echo       SYNCWITHTEAM - REGISTER BROWSER AUTO-WAKE PROTOCOL
echo =====================================================================
echo.

set "TRACKER_BAT=%~dp0Start-Tracker.bat"

reg add "HKCU\Software\Classes\syncwithteam" /ve /d "URL:SyncWithTeam Protocol" /f >nul
reg add "HKCU\Software\Classes\syncwithteam" /v "URL Protocol" /d "" /f >nul
reg add "HKCU\Software\Classes\syncwithteam\shell\open\command" /ve /d "\"%TRACKER_BAT%\" %%1" /f >nul

if %errorlevel% equ 0 (
    echo [OK] syncwithteam:// browser protocol registered successfully!
) else (
    echo [ERROR] Failed to register protocol.
)
echo.
pause
