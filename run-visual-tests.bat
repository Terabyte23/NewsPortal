@echo off
echo ===============================================================
echo   NewsPortal - Selenium-Style Visual E2E Test Runner
echo ===============================================================
echo.
echo Launching Visual Test Runner in Google Chrome / Browser...
echo URL: http://localhost/NewsPortal/tests/visual_runner.php
echo.

if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" "http://localhost/NewsPortal/tests/visual_runner.php"
) else if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" "http://localhost/NewsPortal/tests/visual_runner.php"
) else (
    start http://localhost/NewsPortal/tests/visual_runner.php
)

echo Visual runner opened! Watch the tests execute interactively in the browser.
