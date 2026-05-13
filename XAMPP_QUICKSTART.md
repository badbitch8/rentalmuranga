# XAMPP Quick Start Guide - Murang'a Rental Marketplace

This is a simplified guide for getting started with XAMPP. For complete documentation, see [SETUP_GUIDE.md](SETUP_GUIDE.md).

## Step 1: Install XAMPP

1. Download XAMPP from [apachefriends.org](https://www.apachefriends.org/download.html)
2. Choose version with **PHP 8.2 or higher**
3. Run installer and install to `C:\xampp`
4. Select these components:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin

## Step 2: Configure XAMPP

### Enable PHP Extensions

1. Open `C:\xampp\php\php.ini` in a text editor
2. Find and uncomment (remove `;` at start) these lines:
   ```ini
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=zip
   extension=intl
   ```
3. Save the file

### Add PHP to System PATH

1. Press `Win + X` → System
2. Click "Advanced system settings"
3. Click "Environment Variables"
4. Under "System variables", find and select "Path"
5. Click "Edit" → "New"
6. Add: `C:\xampp\php`
7. Click OK to save

### Start XAMPP Services

1. Open XAMPP Control Panel (as Administrator)
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Both should show green "Running" status

## Step 3: Install Additional Tools

### Install Composer

1. Download from [getcomposer.org](https://getcomposer.org/download/)
2. Run installer
3. When asked for PHP, select: `C:\xampp\php\php.exe`
4. Complete installation

**Verify:**
```bash
composer --version
```

### Install Node.js

1. Download LTS version from [nodejs.org](https://nodejs.org/)
2. Run installer with default settings
3. Complete installation

**Verify:**
```bash
node --version
npm --version
```

### Install Git

1. Download from [git-scm.com](https://git-scm.com/download/win)
2. Run installer with default settings
3. Complete installation

**Verify:**
```bash
git --version
```

## Step 4: Create Database

### Using phpMyAdmin (Easiest)

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click "New" in left sidebar
3. Database name: `muranga_rentals`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Using MySQL Command Line

1. Open XAMPP Control Panel
2. Click "Shell" button
3. Run these commands:
   ```bash
   mysql -u root
   CREATE DATABASE muranga_rentals;
   EXIT;
   ```

## Step 5: Create Laravel Project

Open Command Prompt or PowerShell and run:

```bash
# Navigate to your projects folder
cd C:\Users\HP\Rental

# Create Laravel project
composer create-project laravel/laravel muranga-rentals

# Navigate into project
cd muranga-rentals

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

## Step 6: Configure Database Connection

Edit `.env` file in your project root:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=
```

**Important:** Leave `DB_PASSWORD` empty (XAMPP's MySQL has no password by default)

## Step 7: Install Project Dependencies

```bash
# Install PHP packages
composer require laravel/sanctum
composer require laravel/telescope
composer require spatie/laravel-permission
composer require intervention/image
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel

# Install Node packages
npm install
npm install vue@next
npm install @vitejs/plugin-vue
npm install pinia
npm install vue-router@4
npm install axios
npm install -D tailwindcss postcss autoprefixer
npm install @headlessui/vue
npm install @heroicons/vue

# Initialize Tailwind
npx tailwindcss init -p
```

## Step 8: Run Migrations

```bash
# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

## Step 9: Start Development Servers

Open **3 separate terminal windows**:

**Terminal 1 - Laravel:**
```bash
cd C:\Users\HP\Rental\muranga-rentals
php artisan serve
```

**Terminal 2 - Vite (Frontend):**
```bash
cd C:\Users\HP\Rental\muranga-rentals
npm run dev
```

**Terminal 3 - Queue Worker:**
```bash
cd C:\Users\HP\Rental\muranga-rentals
php artisan queue:work
```

## Access Your Application

- **Main App:** http://localhost:8000
- **phpMyAdmin:** http://localhost/phpmyadmin
- **Telescope:** http://localhost:8000/telescope (after setup)

## Common XAMPP Issues & Solutions

### Apache Won't Start

**Problem:** Port 80 is already in use

**Solution 1 - Change Apache Port:**
1. XAMPP Control Panel → Config → Apache (httpd.conf)
2. Find: `Listen 80`
3. Change to: `Listen 8080`
4. Find: `ServerName localhost:80`
5. Change to: `ServerName localhost:8080`
6. Save and restart Apache
7. Access via: `http://localhost:8080/phpmyadmin`

**Solution 2 - Stop Conflicting App:**
- Close Skype (uses port 80)
- Stop IIS: `net stop was /y` (run as admin)

### MySQL Won't Start

**Problem:** Port 3306 is already in use

**Solution:**
1. Open Task Manager
2. Find other MySQL processes
3. End those processes
4. Or change MySQL port in XAMPP config

### PHP Extensions Not Loading

**Problem:** Extensions not working

**Solution:**
1. Edit `C:\xampp\php\php.ini`
2. Ensure extension path is correct:
   ```ini
   extension_dir = "C:\xampp\php\ext"
   ```
3. Uncomment required extensions
4. Restart Apache in XAMPP

### Permission Errors

**Problem:** Can't write to storage

**Solution:**
```bash
# In your Laravel project
php artisan storage:link
```

If still having issues, run Command Prompt as Administrator.

## Quick Commands Reference

### XAMPP Commands
```bash
# Start services (from XAMPP Control Panel)
- Click "Start" for Apache
- Click "Start" for MySQL

# Stop services
- Click "Stop" for Apache
- Click "Stop" for MySQL

# Open Shell
- Click "Shell" button in XAMPP Control Panel
```

### Laravel Commands
```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create controller
php artisan make:controller ControllerName

# Create model
php artisan make:model ModelName

# Run tests
php artisan test
```

### Composer Commands
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Add package
composer require package/name

# Remove package
composer remove package/name

# Dump autoload
composer dump-autoload
```

### NPM Commands
```bash
# Install dependencies
npm install

# Run development server
npm run dev

# Build for production
npm run build

# Run tests
npm run test
```

## Database Backup (XAMPP)

### Manual Backup via phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Select `muranga_rentals` database
3. Click "Export" tab
4. Choose "Quick" export method
5. Format: SQL
6. Click "Go"
7. Save the .sql file

### Restore Database
1. Go to http://localhost/phpmyadmin
2. Select `muranga_rentals` database
3. Click "Import" tab
4. Choose your .sql file
5. Click "Go"

### Command Line Backup
```bash
# Backup
mysqldump -u root muranga_rentals > backup.sql

# Restore
mysql -u root muranga_rentals < backup.sql
```

## Next Steps

1. ✅ XAMPP installed and running
2. ✅ Database created
3. ✅ Laravel project created
4. ✅ Dependencies installed
5. ✅ Development servers running

**Now proceed to:**
- [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) - Start building features
- [TECHNICAL_SPECIFICATION.md](TECHNICAL_SPECIFICATION.md) - API reference
- [PROJECT_PLAN.md](PROJECT_PLAN.md) - Overall architecture

## Useful XAMPP Locations

- **XAMPP Root:** `C:\xampp`
- **PHP:** `C:\xampp\php`
- **PHP Config:** `C:\xampp\php\php.ini`
- **Apache Config:** `C:\xampp\apache\conf\httpd.conf`
- **MySQL Data:** `C:\xampp\mysql\data`
- **htdocs:** `C:\xampp\htdocs` (for Apache projects)
- **Logs:** `C:\xampp\apache\logs` and `C:\xampp\mysql\data`

## Getting Help

- **XAMPP Issues:** [apachefriends.org/community.html](https://www.apachefriends.org/community.html)
- **Laravel Issues:** [laravel.com/docs](https://laravel.com/docs)
- **Project Issues:** See [SETUP_GUIDE.md](SETUP_GUIDE.md) troubleshooting section

---

**Happy Coding! 🚀**

For detailed setup instructions, see [SETUP_GUIDE.md](SETUP_GUIDE.md)