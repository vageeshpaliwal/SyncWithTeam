@echo off
title SyncWithTeam Desktop Tracker
color 0A
cls
echo =====================================================================
echo       SYNCWITHTEAM - DESKTOP SCREENSHOT TRACKER
echo =====================================================================
echo.
echo Running screenshot tracker in background...
echo (You can minimize this window or keep it open)
echo.

where node >nul 2>nul
if %errorlevel% equ 0 (
    node tracker.cjs
    goto end
)

where python >nul 2>nul
if %errorlevel% equ 0 (
    python tracker.py
    goto end
)

echo [ERROR] Node.js or Python is required.
pause

:end
