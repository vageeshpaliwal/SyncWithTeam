@echo off
title Unblock Tracker Files
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-ChildItem -Path '%~dp0*' | Unblock-File; Write-Host '[OK] All files unblocked successfully!'"
pause
