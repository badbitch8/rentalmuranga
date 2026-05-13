# Fix PHP GD Extension Error

## Problem
Composer install is failing because the PHP GD extension is not enabled in XAMPP.

Error: `ext-gd * -> it is missing from your system`

## Solution

### Step 1: Enable GD Extension in php.ini

1. **Open php.ini file**:
   - Location: `C:\xampp\php\php.ini`
   - Open with Notepad or any text editor (as Administrator)

2. **Find the GD extension line**:
   - Search for: `;extension=gd`
   - It should be around line 900-950

3. **Remove the semicolon** to uncomment it:
   ```ini
   ; Change this:
   ;extension=gd
   
   ; To this:
   extension=gd
   ```

4. **Save the file**

### Step 2: Restart Apache

1. Open XAMPP Control Panel
2. Stop Apache if it's running
3. Start Apache again

### Step 3: Verify GD is Enabled

Run this command in terminal:
```bash
php -m | findstr gd
```

You should see `gd` in the output.

### Step 4: Run Composer Install Again

```bash
cd muranga-rentals
php ../composer.phar install
```

## Alternative: Ignore GD Extension (Not Recommended)

If you don't need Excel export functionality right now, you can temporarily ignore the GD requirement:

```bash
cd muranga-rentals
php ../composer.phar install --ignore-platform-req=ext-gd
```

**Note**: This will install packages but Excel export features won't work until GD is properly enabled.

## What is GD Extension?

The GD extension is used for:
- Image manipulation
- Creating charts and graphs
- Excel file generation with images
- QR code generation

It's required by the `maatwebsite/excel` package for generating Excel files with images.

## Troubleshooting

### If GD line doesn't exist in php.ini:

Add this line in the extensions section:
```ini
extension=gd
```

### If still not working:

1. Check if `php_gd.dll` exists in `C:\xampp\php\ext\`
2. If missing, you may need to reinstall XAMPP or download the DLL
3. Ensure you're editing the correct php.ini (check with `php --ini`)

### Check which php.ini is being used:

```bash
php --ini
```

This will show you the exact php.ini file being used.

## Quick Fix Script

Create a file `enable-gd.ps1` and run it as Administrator:

```powershell
# PowerShell script to enable GD extension
$phpIniPath = "C:\xampp\php\php.ini"
$content = Get-Content $phpIniPath
$content = $content -replace ';extension=gd', 'extension=gd'
Set-Content $phpIniPath $content
Write-Host "GD extension enabled. Please restart Apache in XAMPP Control Panel."
```

Run with:
```powershell
powershell -ExecutionPolicy Bypass -File enable-gd.ps1