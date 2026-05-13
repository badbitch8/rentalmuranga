# Pre-Build Checklist - Murang'a Rental Marketplace

Before we start building, let's ensure all prerequisites are installed.

## ✅ Current Status

### Installed
- ✅ **PHP 8.2.12** - Installed and working
- ✅ **XAMPP** - Assumed installed (PHP is from XAMPP)

### Not Installed Yet
- ❌ **Composer** - Required for Laravel
- ❓ **Node.js & npm** - Need to verify
- ❓ **Git** - Need to verify
- ❓ **MySQL** - Need to verify (should be in XAMPP)

## 🚀 Installation Steps

### Step 1: Install Composer

**Download and Install:**
1. Go to [getcomposer.org/download](https://getcomposer.org/download/)
2. Download **Composer-Setup.exe** for Windows
3. Run the installer
4. When asked for PHP location, select: `C:\xampp\php\php.exe`
5. Complete the installation
6. **Restart your terminal/command prompt**

**Verify Installation:**
```bash
composer --version
```

You should see something like: `Composer version 2.x.x`

### Step 2: Install Node.js and npm

**Download and Install:**
1. Go to [nodejs.org](https://nodejs.org/)
2. Download the **LTS version** (Long Term Support)
3. Run the installer
4. Use default settings
5. Complete the installation
6. **Restart your terminal/command prompt**

**Verify Installation:**
```bash
node --version
npm --version
```

You should see version numbers for both.

### Step 3: Install Git (if not installed)

**Download and Install:**
1. Go to [git-scm.com/download/win](https://git-scm.com/download/win)
2. Download the installer
3. Run the installer with default settings
4. Complete the installation
5. **Restart your terminal/command prompt**

**Verify Installation:**
```bash
git --version
```

### Step 4: Verify XAMPP MySQL

**Start MySQL:**
1. Open XAMPP Control Panel
2. Click "Start" for MySQL
3. It should show green "Running" status

**Verify phpMyAdmin:**
1. Open browser
2. Go to: `http://localhost/phpmyadmin`
3. You should see the phpMyAdmin interface

## 📋 Quick Verification Script

After installing everything, run these commands to verify:

```bash
# Check PHP
php --version

# Check Composer
composer --version

# Check Node.js
node --version

# Check npm
npm --version

# Check Git
git --version
```

## 🎯 Once Everything is Installed

After all prerequisites are installed, we'll proceed with:

1. **Create Laravel Project**
   ```bash
   composer create-project laravel/laravel muranga-rentals
   ```

2. **Set Up Database**
   - Create database in phpMyAdmin
   - Configure .env file

3. **Install Dependencies**
   - PHP packages via Composer
   - JavaScript packages via npm

4. **Start Building Features**
   - Database migrations
   - Models and controllers
   - Frontend components

## 💡 Installation Tips

### Composer Installation
- **Important:** Point to XAMPP's PHP: `C:\xampp\php\php.exe`
- If you get errors, run installer as Administrator
- Restart terminal after installation

### Node.js Installation
- Choose LTS version (more stable)
- Default installation path is fine
- Includes npm automatically

### Common Issues

**Issue: "Command not found" after installation**
- Solution: Restart your terminal/command prompt
- Or restart VS Code if using integrated terminal

**Issue: Composer can't find PHP**
- Solution: Manually add PHP to system PATH
- Path: `C:\xampp\php`

**Issue: npm install fails**
- Solution: Run terminal as Administrator
- Or use: `npm install --legacy-peer-deps`

## 📞 Need Help?

If you encounter any issues during installation:

1. Check [XAMPP_QUICKSTART.md](XAMPP_QUICKSTART.md) for detailed XAMPP setup
2. Check [SETUP_GUIDE.md](SETUP_GUIDE.md) for complete setup guide
3. Search for the specific error message online
4. Most installation issues are solved by:
   - Running as Administrator
   - Restarting terminal
   - Checking system PATH

## ⏭️ Next Steps

Once all prerequisites are installed and verified:

1. ✅ Confirm all tools are working
2. 🚀 Create Laravel project
3. 🗄️ Set up database
4. 💻 Start building features

---

**Let me know when you've installed Composer, Node.js, and Git, and we'll start building! 🚀**