@echo off
title Auto-WhatsApp Server (Port 8001)
color 0A

echo ========================================================
echo   Auto-WhatsApp Server Launcher
echo   Laravel Port: 8001  |  Baileys Service Port: 3000
echo ========================================================
echo.

cd /d "%~dp0baileys-service"
echo Starting Baileys Node Service on port 3000...
start /B node server.js

cd /d "%~dp0core"
echo Starting Laravel Server on http://127.0.0.1:8001...
php artisan serve --host=127.0.0.1 --port=8001

pause
