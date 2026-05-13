# Murang'a Rentals - Setup Instructions

## Prerequisites Completed ✅
- ✅ XAMPP installed and running
- ✅ Composer installed
- ✅ Laravel project created
- ✅ Database configured in .env

## Next Steps to Get the API Running

### 1. Install Laravel Sanctum (API Authentication)

```bash
cd muranga-rentals
composer require laravel/sanctum
```

### 2. Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. Create Database in phpMyAdmin

1. Open http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Database name: `muranga_rentals`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations

This will create all 15 tables in your database:

```bash
php artisan migrate
```

Expected output:
```
Migration table created successfully.
Migrating: 2024_01_01_000001_create_users_table
Migrated:  2024_01_01_000001_create_users_table
Migrating: 2024_01_01_000002_create_properties_table
Migrated:  2024_01_01_000002_create_properties_table
... (and so on for all 15 tables)
```

### 6. Create Storage Link (for file uploads)

```bash
php artisan storage:link
```

### 7. Start the Development Server

```bash
php artisan serve
```

The API will be available at: http://localhost:8000

## Testing the API

### 1. Register a New User (Landlord)

**Endpoint:** `POST http://localhost:8000/api/v1/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0712345678",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "landlord",
    "address": "Murang'a Town",
    "city": "Murang'a",
    "county": "Murang'a"
}
```

**Expected Response (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "0712345678",
            "role": "landlord",
            "is_verified": false
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### 2. Login

**Endpoint:** `POST http://localhost:8000/api/v1/login`

**Body (JSON):**
```json
{
    "email": "john@example.com",
    "password": "Password123!"
}
```

### 3. Get Current User Profile

**Endpoint:** `GET http://localhost:8000/api/v1/me`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

### 4. Test with Postman or Thunder Client

1. Install Postman or Thunder Client (VS Code extension)
2. Create a new collection "Murang'a Rentals API"
3. Add the above requests
4. Save the token from registration/login
5. Use it in subsequent requests

## What's Been Built So Far

### ✅ Database Layer (100% Complete)
- 15 migration files created
- All tables with proper relationships
- Indexes for performance
- Soft deletes where needed

### ✅ Model Layer (100% Complete)
- 15 Eloquent models with full relationships
- Helper methods for business logic
- Query scopes for filtering
- Type casting configured

### ✅ Authentication System (100% Complete)
- User registration with validation
- Login with token generation
- Logout functionality
- Profile management
- Password change
- Role-based access (landlord/tenant/admin)
- Admin middleware for protected routes

### ✅ API Routes (Defined)
- Authentication routes
- Property management routes
- Booking routes
- Payment routes
- Review routes
- Maintenance request routes
- Message routes
- Notification routes
- Admin routes

## Next Steps for Development

### Phase 1: Property Management (Next)
1. Create PropertyController
2. Implement CRUD operations
3. Add image upload functionality
4. Implement search and filters

### Phase 2: Booking System
1. Create BookingController
2. Implement booking workflow
3. Add contract generation
4. Digital signature support

### Phase 3: Payment Integration
1. Create PaymentController
2. Integrate M-Pesa Daraja API
3. Handle payment callbacks
4. Payment reconciliation

### Phase 4: Reviews & Ratings
1. Create ReviewController
2. Rating system
3. Landlord responses
4. Review verification

### Phase 5: Real-time Features
1. Message system
2. Notifications
3. WebSocket integration
4. Live chat

## Project Structure

```
muranga-rentals/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── AuthController.php ✅
│   │   └── Middleware/
│   │       └── AdminMiddleware.php ✅
│   └── Models/
│       ├── User.php ✅
│       ├── Property.php ✅
│       ├── PropertyImage.php ✅
│       ├── Booking.php ✅
│       ├── Payment.php ✅
│       ├── Review.php ✅
│       ├── MaintenanceRequest.php ✅
│       ├── Message.php ✅
│       ├── Notification.php ✅
│       ├── SavedSearch.php ✅
│       ├── Favorite.php ✅
│       ├── PropertyView.php ✅
│       ├── Dispute.php ✅
│       ├── Referral.php ✅
│       └── ActivityLog.php ✅
├── database/
│   └── migrations/ (15 files) ✅
├── routes/
│   └── api.php ✅
└── .env (configured) ✅
```

## Common Issues & Solutions

### Issue: "Class 'Laravel\Sanctum\...' not found"
**Solution:** Run `composer require laravel/sanctum`

### Issue: Migration fails
**Solution:** 
1. Check database connection in .env
2. Ensure database exists in phpMyAdmin
3. Run `php artisan migrate:fresh` to start over

### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Solution:** 
1. Start XAMPP MySQL service
2. Check DB_HOST=127.0.0.1 in .env

### Issue: Token not working
**Solution:**
1. Ensure Sanctum is installed
2. Run migrations
3. Use correct header: `Authorization: Bearer TOKEN`

## Environment Variables

Your `.env` file should have:

```env
APP_NAME="Murang'a Rentals"
APP_ENV=local
APP_KEY=base64:... (generated)
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_DRIVER=database
```

## Support

If you encounter any issues:
1. Check the error logs in `storage/logs/laravel.log`
2. Verify XAMPP services are running
3. Ensure all migrations ran successfully
4. Check API endpoint URLs are correct

---

**Status:** Foundation Complete ✅
**Next:** Implement Property Management Controller