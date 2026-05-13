# 🚀 Murang'a Rentals - Quick Start Guide

Get your rental marketplace up and running in **5 minutes**!

---

## ✅ Prerequisites Check

Before starting, ensure you have:
- ✅ XAMPP installed (MySQL & Apache)
- ✅ Composer installed
- ✅ PHP 8.2+ installed
- ✅ Laravel project created (`muranga-rentals` folder)

---

## 📋 Step-by-Step Setup

### **Step 1: Start XAMPP Services** (30 seconds)

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Wait for both to show **green** status

✅ **Verify:** Both services should show "Running"

---

### **Step 2: Create Database** (1 minute)

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Database name: `muranga_rentals`
4. Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

✅ **Verify:** You should see `muranga_rentals` in the database list

---

### **Step 3: Configure Environment** (30 seconds)

The `.env` file is already configured! Just verify these settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=
```

✅ **Verify:** File `muranga-rentals/.env` exists with correct settings

---

### **Step 4: Run Migrations** (1 minute)

Open **Command Prompt** or **PowerShell** in the `muranga-rentals` folder:

```bash
# Navigate to project folder
cd c:\Users\HP\Rental\muranga-rentals

# Run migrations to create tables
php artisan migrate
```

**Expected Output:**
```
Migration table created successfully.
Migrating: 2024_01_01_000001_create_users_table
Migrated:  2024_01_01_000001_create_users_table (XX.XXms)
...
(15 migrations will run)
```

✅ **Verify:** All 15 migrations should complete successfully

---

### **Step 5: Seed Test Data** (1 minute)

```bash
# Populate database with test data
php artisan db:seed
```

**Expected Output:**
```
Seeding: Database\Seeders\UserSeeder
Users seeded successfully!
Admin: admin@murangarentals.com / password123
...
Seeding: Database\Seeders\PropertySeeder
Properties seeded successfully!
Created 8 properties with images.
```

✅ **Verify:** You should see success messages for all seeders

---

### **Step 6: Create Storage Link** (10 seconds)

```bash
# Create symbolic link for file uploads
php artisan storage:link
```

**Expected Output:**
```
The [public/storage] link has been connected to [storage/app/public].
```

✅ **Verify:** Link created successfully

---

### **Step 7: Start Laravel Server** (10 seconds)

```bash
# Start the development server
php artisan serve
```

**Expected Output:**
```
INFO  Server running on [http://127.0.0.1:8000].

Press Ctrl+C to stop the server
```

✅ **Verify:** Server is running at http://127.0.0.1:8000

---

## 🎉 You're Ready!

Your Murang'a Rentals API is now running! Keep the terminal open.

---

## 🧪 Test the API

### **Option 1: Using PowerShell** (Recommended)

Open a **NEW** PowerShell window:

```powershell
# Test 1: Register a new user
$body = @{
    name = "Test User"
    email = "test@example.com"
    phone_number = "254700000000"
    password = "password123"
    password_confirmation = "password123"
    role = "tenant"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/register" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { ... },
    "token": "1|xxxxx..."
  }
}
```

```powershell
# Test 2: Get all properties
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties" -Method GET
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ... 8 properties ... ]
  }
}
```

---

### **Option 2: Using Browser**

1. Open browser
2. Go to: `http://127.0.0.1:8000/api/v1/properties`
3. You should see JSON response with properties

---

### **Option 3: Using Postman**

1. Open Postman
2. Import collection from `POSTMAN_JSON_EXAMPLES.md`
3. Test endpoints

---

## 📊 Test Data Available

### **Users Created:**

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Admin | admin@murangarentals.com | password123 | Platform administrator |
| Landlord | john.kamau@gmail.com | password123 | Has 3 properties |
| Landlord | mary.wanjiku@gmail.com | password123 | Has 2 properties |
| Landlord | peter.mwangi@gmail.com | password123 | Has 3 properties |
| Tenant | james.omondi@student.mut.ac.ke | password123 | MUT student |
| Tenant | grace.akinyi@student.mut.ac.ke | password123 | MUT student |
| Tenant | david.kipchoge@student.mut.ac.ke | password123 | MUT student |

### **Properties Created:**

- **8 properties** across Murang'a County
- Various types: Bedsitters, Studios, 1BR, 2BR, 3BR
- Prices: KES 2,500 - 18,000 per month
- All within 2km of MUT

---

## 🔑 Quick API Test Flow

### **1. Register & Login**

```powershell
# Register
$register = @{
    name = "John Doe"
    email = "john@example.com"
    phone_number = "254711111111"
    password = "password123"
    password_confirmation = "password123"
    role = "tenant"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/register" `
    -Method POST -Body $register -ContentType "application/json"

# Save token
$token = $response.data.token
```

### **2. Browse Properties**

```powershell
# Get all properties
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties" -Method GET

# Search properties
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties?min_price=5000&max_price=10000&bedrooms=1" -Method GET
```

### **3. View Property Details**

```powershell
# Get property by ID
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties/1" -Method GET
```

### **4. Create Booking** (Requires Authentication)

```powershell
$booking = @{
    property_id = 1
    check_in_date = "2026-06-01"
    check_out_date = "2026-12-01"
    duration_months = 6
    message = "I'm interested in this property"
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/bookings" `
    -Method POST -Body $booking -Headers $headers
```

---

## 📱 Available Endpoints

### **Public Endpoints** (No authentication required)
- `POST /api/v1/register` - Register new user
- `POST /api/v1/login` - Login
- `GET /api/v1/properties` - List properties
- `GET /api/v1/properties/{id}` - View property

### **Protected Endpoints** (Requires Bearer token)
- `GET /api/v1/me` - Get current user
- `POST /api/v1/properties` - Create property (landlord)
- `POST /api/v1/bookings` - Create booking
- `POST /api/v1/payments/mpesa/initiate` - Make payment
- `POST /api/v1/reviews` - Leave review
- `POST /api/v1/maintenance-requests` - Submit maintenance
- `POST /api/v1/messages` - Send message
- `GET /api/v1/notifications` - Get notifications

**Total: 60+ endpoints available!**

---

## 🛠️ Common Commands

```bash
# Start server
php artisan serve

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Reset database (fresh start)
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear

# View routes
php artisan route:list

# Generate key
php artisan key:generate

# Create storage link
php artisan storage:link
```

---

## 🐛 Troubleshooting

### **Problem: "Access denied for user 'root'"**
**Solution:** 
- Open XAMPP Control Panel
- Click "Config" next to MySQL
- Select "my.ini"
- Find `[mysqld]` section
- Ensure no password is set for root
- Restart MySQL

### **Problem: "Base table or view not found"**
**Solution:**
```bash
php artisan migrate:fresh --seed
```

### **Problem: "Class 'App\Models\User' not found"**
**Solution:**
```bash
composer dump-autoload
```

### **Problem: "419 Page Expired"**
**Solution:**
- This is for web routes, not API
- API routes don't need CSRF token
- Use `/api/v1/` prefix for all API calls

### **Problem: "Server not responding"**
**Solution:**
- Check if `php artisan serve` is running
- Check if XAMPP MySQL is running
- Try: `php artisan serve --host=127.0.0.1 --port=8000`

---

## 📚 Next Steps

1. ✅ **Test all endpoints** using Postman or PowerShell
2. ✅ **Read API documentation** in `TECHNICAL_SPECIFICATION.md`
3. ✅ **Check examples** in `POSTMAN_JSON_EXAMPLES.md`
4. ⏳ **Build frontend** with Vue.js
5. ⏳ **Deploy to production**

---

## 🎓 Learning Resources

- **Laravel Docs:** https://laravel.com/docs
- **Laravel Sanctum:** https://laravel.com/docs/sanctum
- **API Testing:** See `API_TESTING_GUIDE.md`
- **Full Documentation:** See `DEVELOPMENT_SUMMARY.md`

---

## 💡 Pro Tips

1. **Keep terminal open** - Don't close the `php artisan serve` window
2. **Use Postman** - Easier for testing complex requests
3. **Check logs** - Errors are in `storage/logs/laravel.log`
4. **Test incrementally** - Test each endpoint before moving to next
5. **Save tokens** - Store authentication tokens for subsequent requests

---

## 🎉 Success Checklist

- [ ] XAMPP MySQL running
- [ ] Database `muranga_rentals` created
- [ ] Migrations completed (15 tables)
- [ ] Test data seeded (users & properties)
- [ ] Laravel server running
- [ ] API responding to requests
- [ ] Can register new user
- [ ] Can view properties
- [ ] Can create booking

**All checked? Congratulations! Your rental marketplace is ready!** 🚀

---

## 📞 Need Help?

Check these files:
- `SETUP_INSTRUCTIONS.md` - Detailed setup guide
- `API_TESTING_GUIDE.md` - API testing examples
- `TECHNICAL_SPECIFICATION.md` - Complete API documentation
- `DEVELOPMENT_SUMMARY.md` - Project overview

---

**Built with ❤️ for Murang'a University Students**

**Last Updated:** May 5, 2026