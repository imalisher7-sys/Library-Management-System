@echo off
title BlockShelf Library - Setup
color 0A

echo.
echo  ============================================
echo   BlockShelf Library - One-Click XAMPP Setup
echo  ============================================
echo.

if not exist "C:\xampp\htdocs" (
    echo  [ERROR] XAMPP not found at C:\xampp
    echo.
    echo  Install XAMPP first from: https://www.apachefriends.org/
    echo  Use the default install location: C:\xampp
    echo.
    pause
    exit /b 1
)

echo  [1/2] Copying project to C:\xampp\htdocs\lirary ...
if exist "C:\xampp\htdocs\lirary" rmdir /S /Q "C:\xampp\htdocs\lirary"
mkdir "C:\xampp\htdocs\lirary"
robocopy "%~dp0" "C:\xampp\htdocs\lirary" /E /XD .git /NFL /NDL /NJH /NJS /nc /ns /np >nul

echo  [2/2] Done! Files copied successfully.
echo.
echo  --------------------------------------------
echo   NOW DO THIS:
echo   1. Open XAMPP Control Panel
echo   2. Click START on Apache
echo   3. Click START on MySQL
echo   4. Open: http://localhost/lirary/
echo.
echo   Database creates automatically - no import needed!
echo  --------------------------------------------
echo.

start http://localhost/lirary/
pause
