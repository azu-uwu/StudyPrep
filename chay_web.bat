@echo off
cd /d "%~dp0"
title StudyPrep Web Server - http://127.0.0.1:8000

echo =======================================================
echo              STUDYPREP WEB SERVER
echo =======================================================
echo.

start "" "http://127.0.0.1:8000"

netstat -ano | findstr ":8000 " >nul
if %errorlevel% equ 0 (
    echo Server is ALREADY running at http://127.0.0.1:8000
    echo Browser has been opened!
    echo.
    ping 127.0.0.1 -n 4 >nul
    exit /b 0
)

echo Starting Laravel server on port 8000...
echo Do not close this window while using the website.
echo =======================================================
echo.

"D:\laragon\bin\php\php-8.5\php.exe" artisan serve --host=127.0.0.1 --port=8000

pause
