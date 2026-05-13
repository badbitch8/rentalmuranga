# Setup Guide - Murang'a Rental Marketplace

## Prerequisites Installation

Before starting the project, you need to install the following tools:

### 1. Install XAMPP (Includes PHP 8.2+ and MySQL)

**Windows:**
1. Download XAMPP from [apachefriends.org](https://www.apachefriends.org/download.html)
2. Choose the version with PHP 8.2 or higher
3. Run the installer
4. Select components to install:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin
   - ❌ FileZilla (optional)
   - ❌ Mercury (optional)
   - ❌ Tomcat (optional)
5. Install to default location: `C:\xampp`
6. Complete the installation

**Configure PHP in XAMPP:**
1. Open `C:\xampp\php\php.ini`
2. Enable required extensions (remove the semicolon `;` at the start):
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

**Add PHP to System PATH:**
1. Open System Properties → Environment Variables
2. Under System Variables, find `Path`
3. Click Edit → New
4. Add: `C:\xampp\php`
5. Click OK to save

**Start XAMPP Services:**
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL

**Verify installation:**
```bash
php --version
mysql --version
```

### 2. Install Composer

**Windows:**
1. Download Composer installer from [getcomposer.org](https://getcomposer.org/download/)
2. Run the installer
3. When prompted, select PHP executable: `C:\xampp\php\php.exe`
4. Follow the installation wizard

**Verify installation:**
```bash
composer --version
```

### 3. Install Node.js and npm

**Windows:**
1. Download Node.js LTS from [nodejs.org](https://nodejs.org/)
2. Run the installer
3. Follow the installation wizard

**Verify installation:**
```bash
node --version
npm --version
```

### 4. Install Redis (Optional but Recommended)

**Windows:**
1. Download Redis for Windows from [github.com/microsoftarchive/redis](https://github.com/microsoftarchive/redis/releases)
2. Extract and run `redis-server.exe`

Or use Docker:
```bash
docker run -d -p 6379:6379 redis:alpine
```

### 6. Install Git

**Windows:**
1. Download Git from [git-scm.com](https://git-scm.com/download/win)
2. Run the installer
3. Use default settings

**Verify installation:**
```bash
git --version
```

## Project Setup

Once all prerequisites are installed, follow these steps:

### Step 1: Create Laravel Project

```bash
# Navigate to your projects directory
cd c:/Users/HP/Rental

# Create new Laravel project
composer create-project laravel/laravel muranga-rentals

# Navigate into project
cd muranga-rentals
```

### Step 2: Install Backend Dependencies

```bash
# Install Laravel packages
composer require laravel/sanctum
composer require laravel/telescope
composer require spatie/laravel-permission
composer require intervention/image
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
composer require laravel/scout
composer require meilisearch/meilisearch-php

# Install development packages
composer require --dev laravel/pint
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev fakerphp/faker
```

### Step 3: Install Frontend Dependencies

```bash
# Install Vue.js and related packages
npm install vue@next
npm install @vitejs/plugin-vue
npm install pinia
npm install vue-router@4
npm install axios

# Install UI framework
npm install -D tailwindcss postcss autoprefixer
npm install @headlessui/vue
npm install @heroicons/vue

# Initialize Tailwind CSS
npx tailwindcss init -p

# Install development tools
npm install --save-dev @vue/test-utils
npm install --save-dev vitest
```

### Step 4: Configure Environment

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database

**Using XAMPP phpMyAdmin:**
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click on "New" in the left sidebar
3. Enter database name: `muranga_rentals`
4. Choose collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Or using MySQL command line:**
```bash
# Open XAMPP Control Panel and click "Shell" button
# Then run:
mysql -u root
CREATE DATABASE muranga_rentals;
EXIT;
```

**Edit `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=
```

**Note:** XAMPP's MySQL root user has no password by default. Leave `DB_PASSWORD` empty.

### Step 6: Install and Configure Sanctum

```bash
# Publish Sanctum configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

### Step 7: Install Telescope (Development Only)

```bash
# Install Telescope
php artisan telescope:install

# Run migrations
php artisan migrate
```

### Step 8: Configure Vite for Vue.js

Edit `vite.config.js`:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
```

### Step 9: Configure Tailwind CSS

Edit `tailwind.config.js`:
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### Step 10: Set Up Git Repository

```bash
# Initialize git repository
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial commit: Project setup"

# Add remote repository (replace with your repo URL)
git remote add origin https://github.com/yourusername/muranga-rentals.git

# Push to remote
git push -u origin main
```

## Running the Application

### Development Mode

Open 3 terminal windows:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 3 - Queue Worker:**
```bash
php artisan queue:work
```

Access the application at: `http://localhost:8000`

## Next Steps

After completing the setup, proceed with:

1. **Create Database Migrations** - See `IMPLEMENTATION_GUIDE.md` Phase 1, Week 1
2. **Set Up Models** - See `IMPLEMENTATION_GUIDE.md` Phase 1, Week 2
3. **Implement Authentication** - See `IMPLEMENTATION_GUIDE.md` Phase 1, Week 2
4. **Build Core Features** - Follow the implementation guide step by step

## Troubleshooting

### Common Issues

**Issue: XAMPP Apache won't start**
- Solution: Port 80 might be in use by another application (Skype, IIS)
- Change Apache port in XAMPP Config → Apache (httpd.conf)
- Or stop the conflicting application

**Issue: XAMPP MySQL won't start**
- Solution: Port 3306 might be in use
- Check if another MySQL instance is running
- Stop it from Services (services.msc)

**Issue: Composer not found**
- Solution: Ensure Composer is in your system PATH
- Restart your terminal after installation
- Verify PHP path in Composer settings

**Issue: PHP extensions missing**
- Solution: Enable required extensions in `C:\xampp\php\php.ini`
- Remove semicolon (`;`) before extension lines
- Restart Apache in XAMPP Control Panel

**Issue: MySQL connection failed**
- Solution: Ensure MySQL is running in XAMPP Control Panel
- Verify credentials in `.env` file (default: root with no password)
- Check if port 3306 is correct

**Issue: npm install fails**
- Solution: Clear npm cache: `npm cache clean --force`
- Delete `node_modules` and `package-lock.json`, then retry
- Run terminal as Administrator

**Issue: Port 8000 already in use**
- Solution: Use different port: `php artisan serve --port=8001`
- Or stop the application using port 8000

**Issue: Permission denied errors**
- Solution: Run terminal as Administrator (Windows)
- Set proper permissions on storage and cache directories:
  ```bash
  # In your project directory
  php artisan storage:link
  ```

**Issue: "Class not found" errors**
- Solution: Clear and regenerate autoload files:
  ```bash
  composer dump-autoload
  php artisan clear-compiled
  php artisan cache:clear
  ```

**Issue: Database migration errors**
- Solution: Ensure database exists in phpMyAdmin
- Check database credentials in `.env`
- Try: `php artisan migrate:fresh` (WARNING: This drops all tables)

### Getting Help

- Laravel Documentation: [laravel.com/docs](https://laravel.com/docs)
- Vue.js Documentation: [vuejs.org](https://vuejs.org)
- Project Issues: Create an issue on GitHub
- Community Support: Laravel Discord, Stack Overflow

## Development Tools (Optional)

### Recommended VS Code Extensions

1. **PHP Intelephense** - PHP code intelligence
2. **Laravel Extension Pack** - Laravel development tools
3. **Volar** - Vue.js language support
4. **Tailwind CSS IntelliSense** - Tailwind CSS autocomplete
5. **ESLint** - JavaScript linting
6. **Prettier** - Code formatting
7. **GitLens** - Git integration

### Database Management Tools

1. **phpMyAdmin** - Included with XAMPP (http://localhost/phpmyadmin)
   - Already installed and configured
   - Best for quick database operations
   - Access directly from browser

2. **MySQL Workbench** - Desktop MySQL client
   - Download from [mysql.com](https://dev.mysql.com/downloads/workbench/)
   - Professional database design tool
   - Connect using: Host: 127.0.0.1, Port: 3306, User: root, Password: (empty)

3. **HeidiSQL** - Lightweight MySQL client
   - Download from [heidisql.com](https://www.heidisql.com/)
   - Simple and fast
   - Good alternative to phpMyAdmin

4. **TablePlus** - Modern database client
   - Download from [tableplus.com](https://tableplus.com/)
   - Beautiful UI with dark mode
   - Supports multiple databases

5. **DBeaver** - Universal database tool
   - Download from [dbeaver.io](https://dbeaver.io/)
   - Free and open source
   - Advanced features for developers

### API Testing Tools

1. **Postman** - API development and testing
2. **Insomnia** - REST API client
3. **Thunder Client** - VS Code extension for API testing

## Environment Configuration

### Development Environment Variables

```env
APP_NAME="Murang'a Rentals"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# These will be configured later
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_ENVIRONMENT=sandbox

AT_USERNAME=
AT_API_KEY=

GOOGLE_MAPS_API_KEY=
OPENAI_API_KEY=
```

## Security Checklist

Before going to production:

- [ ] Change all default passwords
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure proper CORS settings
- [ ] Enable HTTPS
- [ ] Set up proper file permissions
- [ ] Configure rate limiting
- [ ] Enable CSRF protection
- [ ] Set up backup system
- [ ] Configure monitoring tools
- [ ] Review and update `.env` file
- [ ] Remove development dependencies

## Performance Optimization

### Production Optimizations

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build
```

## Backup and Recovery

### Database Backup

```bash
# Manual backup
mysqldump -u root -p muranga_rentals > backup.sql

# Restore from backup
mysql -u root -p muranga_rentals < backup.sql
```

### Automated Backups

Consider using:
- Laravel Backup package by Spatie
- Scheduled tasks with Laravel's task scheduler
- Cloud backup services (AWS S3, DigitalOcean Spaces)

## Monitoring and Logging

### Log Files Location

- Application logs: `storage/logs/laravel.log`
- Web server logs: Check your web server configuration
- Database logs: Check MySQL configuration

### Monitoring Tools

- Laravel Telescope (Development)
- Sentry (Production error tracking)
- New Relic (Performance monitoring)
- Custom logging with Monolog

## Support and Resources

### Official Documentation

- Laravel: https://laravel.com/docs
- Vue.js: https://vuejs.org/guide
- Tailwind CSS: https://tailwindcss.com/docs
- Vite: https://vitejs.dev/guide

### Community Resources

- Laravel News: https://laravel-news.com
- Laracasts: https://laracasts.com
- Vue Mastery: https://www.vuemastery.com
- Stack Overflow: Tag questions with `laravel`, `vue.js`

### Project-Specific Help

- Review `PROJECT_PLAN.md` for overall architecture
- Check `TECHNICAL_SPECIFICATION.md` for API details
- Follow `IMPLEMENTATION_GUIDE.md` for step-by-step instructions
- Read `README.md` for project overview

---

**Ready to start building? Follow the implementation guide and happy coding! 🚀**