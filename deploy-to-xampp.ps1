# Deploy BlockShelf Library to XAMPP htdocs
$source = Split-Path -Parent $MyInvocation.MyCommand.Path
$dest = "C:\xampp\htdocs\lirary"

if (-not (Test-Path "C:\xampp\htdocs")) {
    Write-Host "XAMPP not found at C:\xampp" -ForegroundColor Red
    Write-Host "Install XAMPP from https://www.apachefriends.org/ then run START.bat"
    exit 1
}

if (Test-Path $dest) {
    Remove-Item $dest -Recurse -Force
}

Copy-Item $source $dest -Recurse -Exclude @(".git")
Write-Host "Copied to $dest" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Open XAMPP Control Panel"
Write-Host "  2. Start Apache and MySQL"
Write-Host "  3. Open http://localhost/lirary/"
Write-Host ""
Write-Host "Database creates automatically on first visit."
