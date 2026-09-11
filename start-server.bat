@echo off
title Auto-WhatsApp Permanent Dual-Server (Laravel 8001 + Baileys 3000)
color 0A

echo ========================================================
echo   Auto-WhatsApp Permanent Multi-Service Watchdog
echo   Laravel Port: 8001  ^|  Baileys Service Port: 3000
echo ========================================================
echo.

cd /d "%~dp0baileys-service"
echo [1/2] Starting Baileys WhatsApp Engine on port 3000...
start "Baileys WhatsApp Service (Port 3000)" cmd /k "title Baileys Engine && node server.js"

timeout /t 2 /nobreak >nul

cd /d "%~dp0core"
echo [2/2] Starting Laravel Engine on http://127.0.0.1:8001...
start "Laravel Web Engine (Port 8001)" cmd /k "title Laravel Engine && php artisan serve --host=127.0.0.1 --port=8001"

echo.
echo ========================================================
echo   Both services have been launched permanently!
echo   - Web Panel: http://127.0.0.1:8001/admin
echo   - Baileys API: http://127.0.0.1:3000/health
echo ========================================================
echo.
pause
