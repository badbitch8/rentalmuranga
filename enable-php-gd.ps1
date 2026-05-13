# PowerShell Script to Enable PHP GD Extension in XAMPP
# Run as Administrator

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  PHP GD Extension Enabler for XAMPP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Define php.ini path
$phpIniPath = "C:\xampp\php\php.ini"

# Check if php.ini exists
if (-Not (Test-Path $phpIniPath)) {
    Write-Host "ERROR: php.ini not found at $phpIniPath" -ForegroundColor Red
    Write-Host "Please ensure XAMPP is installed in C:\xampp" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Found php.ini at: $phpIniPath" -ForegroundColor Green
Write-Host ""

# Create backup
$backupPath = "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Write-Host "Creating backup: $backupPath" -ForegroundColor Yellow
Copy-Item $phpIniPath $backupPath
Write-Host "Backup created successfully!" -ForegroundColor Green
Write-Host ""

# Read php.ini content
$content = Get-Content $phpIniPath -Raw

# Check if GD is already enabled
if ($content -match "^extension=gd" -or $content -match "^extension=php_gd2.dll") {
    Write-Host "GD extension is already enabled!" -ForegroundColor Green
} else {
    Write-Host "Enabling GD extension..." -ForegroundColor Yellow
    
    # Replace ;extension=gd with extension=gd
    $content = $content -replace ";extension=gd", "extension=gd"
    
    # Also handle old format
    $content = $content -replace ";extension=php_gd2.dll", "extension=gd"
    
    # Save the modified content
    Set-Content $phpIniPath $content -NoNewline
    
    Write-Host "GD extension enabled successfully!" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Next Steps:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. Open XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Stop Apache (if running)" -ForegroundColor White
Write-Host "3. Start Apache again" -ForegroundColor White
Write-Host "4. Run: php -m | findstr gd" -ForegroundColor White
Write-Host "   (to verify GD is loaded)" -ForegroundColor White
Write-Host "5. Run composer install again" -ForegroundColor White
Write-Host ""

# Offer to verify
$verify = Read-Host "Would you like to verify GD extension now? (y/n)"
if ($verify -eq "y" -or $verify -eq "Y") {
    Write-Host ""
    Write-Host "Checking PHP modules..." -ForegroundColor Yellow
    $gdCheck = php -m | Select-String "gd"
    if ($gdCheck) {
        Write-Host "SUCCESS: GD extension is loaded!" -ForegroundColor Green
        Write-Host $gdCheck -ForegroundColor Green
    } else {
        Write-Host "WARNING: GD extension not detected yet." -ForegroundColor Yellow
        Write-Host "Please restart Apache in XAMPP Control Panel." -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Script completed!" -ForegroundColor Cyan
Write-Host ""
Read-Host "Press Enter to exit"

# Made with Bob
