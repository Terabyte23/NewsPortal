@echo off
if "%1"=="--visual" goto visual
if "%1"=="--selenium" goto visual
if "%1"=="--gui" goto visual

C:\xampp\php\php.exe phpunit.phar --testdox %*
exit /b %errorlevel%

:visual
call run-visual-tests.bat