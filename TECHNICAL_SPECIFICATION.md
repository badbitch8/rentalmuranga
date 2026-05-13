# Technical Specification - Murang'a Rental Marketplace

## Database Schema (Detailed)

### 1. users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('landlord', 'tenant', 'admin') NOT NULL DEFAULT 'tenant',
    id_number VARCHAR(50) UNIQUE NULL,
    profile_photo VARCHAR(255) NULL,
    bio TEXT NULL,
    verification_status ENUM('unverified', 'pending', 'verified', 'rejected') DEFAULT 'unverified',
    verification_documents JSON NULL,
    two_factor_secret VARCHAR(255) NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role),
    INDEX idx_verification_status (verification_status)
);
```

### 2. properties
```sql
CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    landlord_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    property_type ENUM('apartment', 'bedsitter', 'single_room', 'double_room', 'studio','one_bedroom', 'two_bedroom', 'three_bedroom', 'four_bedroom', 'five_bedroom', 'six_bedroom', 'seven_bedroom', 'eight_bedroom', 'nine_bedroom', 'ten_bedroom', 'villa', 'townhouse', 'penthouse', 'duplex', 'loft', 'cottage', 'bungalow', 'condo', 'apartment', 'house', 'villa', 'townhouse', 'penthouse', 'duplex', 'loft', 'cottage', 'bungalow', 'condo', 'apartment', 'house') NOT NULL,
    bedrooms INT NOT NULL DEFAULT 1,
    bathrooms INT NOT NULL DEFAULT 1,
    square_feet INT NULL,
    rent_amount DECIMAL(10, 2) NOT NULL,
    deposit_amount DECIMAL(10, 2) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    county VARCHAR(100) DEFAULT 'Murang''a',
    sub_county VARCHAR(100) NULL,
    ward VARCHAR(100) NULL,
    distance_to_mut DECIMAL(5, 2) NULL COMMENT 'Distance in KM',
    amenities JSON NULL COMMENT 'wifi, parking, water, electricity, security, etc',
    rules JSON NULL COMMENT 'no_pets, no_smoking, etc',
    furnishing_status ENUM('furnished', 'semi_furnished', 'unfurnished') DEFAULT 'unfurnished',
    availability_status ENUM('available', 'occupied', 'maintenance', 'unlisted') DEFAULT 'available',
    available_from DATE NULL,
    verification_status ENUM('unverified', 'pending', 'verified', 'rejected') DEFAULT 'unverified',
    verification_notes TEXT NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    views_count INT DEFAULT 0,
    inquiries_count INT DEFAULT 0,
    bookings_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    featured_until TIMESTAMP NULL,
    virtual_tour_url VARCHAR(255) NULL,
    video_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_landlord (landlord_id),
    INDEX idx_property_type (property_type),
    INDEX idx_rent_amount (rent_amount),
    INDEX idx_availability (availability_status),
    INDEX idx_verification (verification_status),
    INDEX idx_location (latitude, longitude),
    INDEX idx_distance_mut (distance_to_mut),
    FULLTEXT idx_search (title, description, address)
);
```

### 3. property_images
```sql
CREATE TABLE property_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255) NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    caption VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_primary (is_primary)
);
```

### 4. bookings
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    landlord_id BIGINT UNSIGNED NOT NULL,
    booking_reference VARCHAR(50) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL COMMENT 'NULL for indefinite',
    lease_duration_months INT NULL,
    status ENUM('pending', 'approved', 'rejected', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    rent_amount DECIMAL(10, 2) NOT NULL,
    deposit_amount DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    deposit_paid BOOLEAN DEFAULT FALSE,
    deposit_paid_at TIMESTAMP NULL,
    first_rent_paid BOOLEAN DEFAULT FALSE,
    first_rent_paid_at TIMESTAMP NULL,
    contract_url VARCHAR(255) NULL,
    contract_signed_at TIMESTAMP NULL,
    tenant_signature TEXT NULL,
    landlord_signature TEXT NULL,
    move_in_date DATE NULL,
    move_out_date DATE NULL,
    cancellation_reason TEXT NULL,
    cancelled_by BIGINT UNSIGNED NULL,
    cancelled_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_landlord (landlord_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);
```

### 5. payments
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('mpesa', 'bank_transfer', 'cash', 'card') NOT NULL,
    payment_type ENUM('deposit', 'rent', 'maintenance', 'penalty', 'refund') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NULL,
    mpesa_receipt VARCHAR(100) NULL,
    mpesa_phone VARCHAR(20) NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    month_paid_for DATE NULL COMMENT 'For rent payments',
    description TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_type (payment_type),
    INDEX idx_transaction (transaction_id)
);
```

### 6. reviews
```sql
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    landlord_id BIGINT UNSIGNED NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    cleanliness_rating INT NULL CHECK (cleanliness_rating >= 1 AND cleanliness_rating <= 5),
    communication_rating INT NULL CHECK (communication_rating >= 1 AND communication_rating <= 5),
    value_rating INT NULL CHECK (value_rating >= 1 AND value_rating <= 5),
    location_rating INT NULL CHECK (location_rating >= 1 AND location_rating <= 5),
    comment TEXT NULL,
    landlord_response TEXT NULL,
    landlord_responded_at TIMESTAMP NULL,
    is_verified BOOLEAN DEFAULT FALSE COMMENT 'Verified stay',
    is_featured BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_rating (rating),
    UNIQUE KEY unique_booking_review (booking_id)
);
```

### 7. maintenance_requests
```sql
CREATE TABLE maintenance_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    landlord_id BIGINT UNSIGNED NOT NULL,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('plumbing', 'electrical', 'structural', 'appliance', 'pest_control', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'acknowledged', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_to VARCHAR(255) NULL COMMENT 'Contractor name',
    assigned_phone VARCHAR(20) NULL,
    estimated_cost DECIMAL(10, 2) NULL,
    actual_cost DECIMAL(10, 2) NULL,
    images JSON NULL,
    resolution_notes TEXT NULL,
    acknowledged_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    tenant_satisfaction_rating INT NULL CHECK (tenant_satisfaction_rating >= 1 AND tenant_satisfaction_rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_landlord (landlord_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
);
```

### 8. messages
```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id VARCHAR(100) NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    receiver_id BIGINT UNSIGNED NOT NULL,
    property_id BIGINT UNSIGNED NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    attachment_url VARCHAR(255) NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_property (property_id),
    INDEX idx_read (read_at)
);
```

### 9. notifications
```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    action_url VARCHAR(255) NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (read_at),
    INDEX idx_type (type)
);
```

### 10. saved_searches
```sql
CREATE TABLE saved_searches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    search_criteria JSON NOT NULL,
    email_alerts BOOLEAN DEFAULT TRUE,
    alert_frequency ENUM('instant', 'daily', 'weekly') DEFAULT 'daily',
    last_alerted_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_active (is_active)
);
```

### 11. favorites
```sql
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    property_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id),
    INDEX idx_user (user_id),
    INDEX idx_property (property_id)
);
```

### 12. property_views
```sql
CREATE TABLE property_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_user (user_id),
    INDEX idx_viewed_at (viewed_at)
);
```

### 13. disputes
```sql
CREATE TABLE disputes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    raised_by BIGINT UNSIGNED NOT NULL,
    against_user BIGINT UNSIGNED NOT NULL,
    dispute_type ENUM('payment', 'property_condition', 'contract_breach', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    evidence JSON NULL,
    status ENUM('open', 'under_review', 'resolved', 'closed') DEFAULT 'open',
    resolution TEXT NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (against_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking (booking_id),
    INDEX idx_raised_by (raised_by),
    INDEX idx_status (status)
);
```

### 14. referrals
```sql
CREATE TABLE referrals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_id BIGINT UNSIGNED NOT NULL,
    referred_user_id BIGINT UNSIGNED NULL,
    referral_code VARCHAR(50) UNIQUE NOT NULL,
    referred_email VARCHAR(255) NULL,
    referred_phone VARCHAR(20) NULL,
    status ENUM('pending', 'registered', 'completed') DEFAULT 'pending',
    reward_amount DECIMAL(10, 2) DEFAULT 0.00,
    reward_paid BOOLEAN DEFAULT FALSE,
    reward_paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_referrer (referrer_id),
    INDEX idx_code (referral_code),
    INDEX idx_status (status)
);
```

### 15. activity_logs
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    model_type VARCHAR(100) NULL,
    model_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created_at (created_at)
);
```

## API Endpoints

### Authentication Endpoints

#### POST /api/auth/register
Register a new user
```json
Request:
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+254712345678",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "tenant"
}

Response:
{
  "success": true,
  "data": {
    "user": {...},
    "token": "bearer_token"
  }
}
```

#### POST /api/auth/login
Login user
```json
Request:
{
  "email": "john@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "data": {
    "user": {...},
    "token": "bearer_token"
  }
}
```

#### POST /api/auth/logout
Logout user (requires authentication)

#### POST /api/auth/verify-email
Verify email address

#### POST /api/auth/verify-phone
Verify phone number with OTP

#### POST /api/auth/forgot-password
Request password reset

#### POST /api/auth/reset-password
Reset password with token

#### POST /api/auth/enable-2fa
Enable two-factor authentication

#### POST /api/auth/verify-2fa
Verify 2FA code

### User Endpoints

#### GET /api/users/profile
Get authenticated user profile

#### PUT /api/users/profile
Update user profile

#### POST /api/users/upload-avatar
Upload profile photo

#### POST /api/users/verify-identity
Submit identity verification documents

#### GET /api/users/{id}
Get user public profile

#### GET /api/users/{id}/properties
Get user's properties

#### GET /api/users/{id}/reviews
Get reviews for user

### Property Endpoints

#### GET /api/properties
List properties with filters
```
Query Parameters:
- page: int
- per_page: int (default: 20)
- property_type: string
- min_rent: decimal
- max_rent: decimal
- bedrooms: int
- bathrooms: int
- latitude: decimal
- longitude: decimal
- radius: decimal (in km)
- distance_to_mut: decimal
- amenities: array
- availability_status: string
- sort_by: string (rent_asc, rent_desc, distance, newest)
- search: string
```

#### GET /api/properties/{id}
Get property details

#### POST /api/properties
Create new property (landlord only)

#### PUT /api/properties/{id}
Update property (landlord only)

#### DELETE /api/properties/{id}
Delete property (landlord only)

#### POST /api/properties/{id}/images
Upload property images

#### DELETE /api/properties/{id}/images/{imageId}
Delete property image

#### POST /api/properties/{id}/favorite
Add property to favorites

#### DELETE /api/properties/{id}/favorite
Remove property from favorites

#### GET /api/properties/{id}/similar
Get similar properties

#### POST /api/properties/{id}/view
Record property view

#### GET /api/properties/{id}/availability
Check property availability

### Booking Endpoints

#### GET /api/bookings
List user's bookings

#### GET /api/bookings/{id}
Get booking details

#### POST /api/bookings
Create new booking request
```json
Request:
{
  "property_id": 1,
  "start_date": "2026-06-01",
  "lease_duration_months": 12,
  "notes": "Looking forward to moving in"
}
```

#### PUT /api/bookings/{id}/approve
Approve booking (landlord only)

#### PUT /api/bookings/{id}/reject
Reject booking (landlord only)

#### PUT /api/bookings/{id}/cancel
Cancel booking

#### POST /api/bookings/{id}/sign-contract
Sign contract digitally

#### GET /api/bookings/{id}/contract
Download contract PDF

### Payment Endpoints

#### GET /api/payments
List user's payments

#### GET /api/payments/{id}
Get payment details

#### POST /api/payments/initiate-mpesa
Initiate M-Pesa payment
```json
Request:
{
  "booking_id": 1,
  "amount": 15000,
  "payment_type": "deposit",
  "phone": "+254712345678"
}
```

#### POST /api/payments/mpesa-callback
M-Pesa callback endpoint (webhook)

#### GET /api/payments/booking/{bookingId}
Get payments for specific booking

#### POST /api/payments/{id}/refund
Process refund (admin only)

### Review Endpoints

#### GET /api/reviews
List reviews

#### GET /api/reviews/{id}
Get review details

#### POST /api/reviews
Create review
```json
Request:
{
  "booking_id": 1,
  "rating": 5,
  "cleanliness_rating": 5,
  "communication_rating": 4,
  "value_rating": 5,
  "location_rating": 5,
  "comment": "Great place to stay!"
}
```

#### PUT /api/reviews/{id}
Update review

#### DELETE /api/reviews/{id}
Delete review

#### POST /api/reviews/{id}/respond
Landlord response to review

#### POST /api/reviews/{id}/helpful
Mark review as helpful

### Maintenance Endpoints

#### GET /api/maintenance-requests
List maintenance requests

#### GET /api/maintenance-requests/{id}
Get maintenance request details

#### POST /api/maintenance-requests
Create maintenance request
```json
Request:
{
  "booking_id": 1,
  "title": "Leaking faucet",
  "description": "Kitchen faucet is leaking",
  "category": "plumbing",
  "priority": "medium",
  "images": ["url1", "url2"]
}
```

#### PUT /api/maintenance-requests/{id}
Update maintenance request

#### PUT /api/maintenance-requests/{id}/acknowledge
Acknowledge request (landlord)

#### PUT /api/maintenance-requests/{id}/start
Start work on request

#### PUT /api/maintenance-requests/{id}/complete
Mark request as completed

#### POST /api/maintenance-requests/{id}/rate
Rate completed maintenance

### Message Endpoints

#### GET /api/messages/conversations
List user's conversations

#### GET /api/messages/conversation/{conversationId}
Get messages in conversation

#### POST /api/messages
Send message
```json
Request:
{
  "receiver_id": 2,
  "property_id": 1,
  "message": "Is this property still available?"
}
```

#### PUT /api/messages/{id}/read
Mark message as read

#### POST /api/messages/upload-attachment
Upload message attachment

### Notification Endpoints

#### GET /api/notifications
List user's notifications

#### GET /api/notifications/unread-count
Get unread notification count

#### PUT /api/notifications/{id}/read
Mark notification as read

#### PUT /api/notifications/mark-all-read
Mark all notifications as read

#### DELETE /api/notifications/{id}
Delete notification

### Search Endpoints

#### POST /api/saved-searches
Save search criteria

#### GET /api/saved-searches
List saved searches

#### PUT /api/saved-searches/{id}
Update saved search

#### DELETE /api/saved-searches/{id}
Delete saved search

#### GET /api/search/suggestions
Get search suggestions based on user behavior

### Analytics Endpoints

#### GET /api/analytics/dashboard
Get dashboard analytics (landlord/admin)

#### GET /api/analytics/property/{id}
Get property-specific analytics

#### GET /api/analytics/revenue
Get revenue analytics

#### GET /api/analytics/occupancy
Get occupancy rates

### Admin Endpoints

#### GET /api/admin/users
List all users with filters

#### PUT /api/admin/users/{id}/verify
Verify user

#### PUT /api/admin/users/{id}/suspend
Suspend user

#### GET /api/admin/properties/pending
List properties pending verification

#### PUT /api/admin/properties/{id}/verify
Verify property

#### GET /api/admin/disputes
List all disputes

#### PUT /api/admin/disputes/{id}/resolve
Resolve dispute

#### GET /api/admin/statistics
Get platform statistics

### Recommendation Endpoints

#### GET /api/recommendations/properties
Get AI-powered property recommendations

#### POST /api/recommendations/feedback
Submit feedback on recommendations

### Referral Endpoints

#### GET /api/referrals/code
Get user's referral code

#### POST /api/referrals/invite
Send referral invitation

#### GET /api/referrals/stats
Get referral statistics

## WebSocket Events (Real-time)

### Chat Events
- `message.sent` - New message sent
- `message.read` - Message marked as read
- `user.typing` - User is typing

### Notification Events
- `notification.new` - New notification received
- `notification.read` - Notification marked as read

### Booking Events
- `booking.approved` - Booking approved
- `booking.rejected` - Booking rejected
- `booking.cancelled` - Booking cancelled

### Payment Events
- `payment.completed` - Payment completed
- `payment.failed` - Payment failed

## File Storage Structure

```
storage/
├── app/
│   ├── public/
│   │   ├── avatars/
│   │   │   └── {user_id}/
│   │   ├── properties/
│   │   │   └── {property_id}/
│   │   │       ├── images/
│   │   │       ├── virtual-tours/
│   │   │       └── videos/
│   │   ├── documents/
│   │   │   ├── contracts/
│   │   │   ├── verification/
│   │   │   └── maintenance/
│   │   └── messages/
│   │       └── attachments/
```

## Environment Variables

```env
# Application
APP_NAME="Murang'a Rental Marketplace"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://muranga-rentals.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muranga_rentals
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@muranga-rentals.com
MAIL_FROM_NAME="${APP_NAME}"

# M-Pesa
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_CALLBACK_URL=
MPESA_ENVIRONMENT=production

# Africa's Talking
AT_USERNAME=
AT_API_KEY=
AT_SENDER_ID=

# Google Maps
GOOGLE_MAPS_API_KEY=

# OpenAI
OPENAI_API_KEY=

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=

# Pusher/Reverb
BROADCAST_DRIVER=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=8080
REVERB_SCHEME=https

# Sentry
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=1.0
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Operation successful",
  "meta": {
    "current_page": 1,
    "total_pages": 10,
    "per_page": 20,
    "total": 200
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

## Rate Limiting

- Authentication endpoints: 5 requests per minute
- General API endpoints: 60 requests per minute
- Search endpoints: 30 requests per minute
- File upload endpoints: 10 requests per minute

## Caching Strategy

### Cache Keys
- `property:{id}` - Property details (TTL: 1 hour)
- `user:{id}:profile` - User profile (TTL: 30 minutes)
- `properties:featured` - Featured properties (TTL: 15 minutes)
- `search:{hash}` - Search results (TTL: 5 minutes)
- `analytics:{type}:{date}` - Analytics data (TTL: 1 day)

### Cache Invalidation
- Property cache invalidated on update/delete
- User cache invalidated on profile update
- Search cache invalidated on new property creation
- Featured properties cache invalidated on feature status change

## Security Measures

### Input Validation
- All inputs validated using Laravel Form Requests
- XSS protection via HTML purification
- SQL injection prevention via Eloquent ORM
- CSRF protection on all state-changing requests

### Authentication
- JWT tokens with 24-hour expiration
- Refresh tokens with 30-day expiration
- Token blacklisting on logout
- Rate limiting on authentication endpoints

### Authorization
- Role-based access control (RBAC)
- Policy-based authorization
- Resource ownership verification
- Admin-only endpoints protection

### Data Protection
- Passwords hashed with bcrypt
- Sensitive data encrypted at rest
- HTTPS enforcement
- Secure cookie settings
- API key rotation policy

## Testing Strategy

### Unit Tests
- Model tests
- Service class tests
- Helper function tests
- Validation rule tests

### Feature Tests
- API endpoint tests
- Authentication flow tests
- Payment integration tests
- Booking workflow tests

### Integration Tests
- Third-party API integration tests
- Database transaction tests
- Queue job tests
- Event listener tests

### End-to-End Tests
- User registration and login flow
- Property listing and booking flow
- Payment processing flow
- Maintenance request flow

## Performance Optimization

### Database Optimization
- Proper indexing on frequently queried columns
- Query optimization with eager loading
- Database query caching
- Connection pooling

### Application Optimization
- Route caching
- Config caching
- View caching
- OPcache enabled
- Lazy loading of relationships

### Frontend Optimization
- Code splitting
- Lazy loading of components
- Image optimization and lazy loading
- Asset minification and compression
- Service worker for offline support

## Monitoring and Logging

### Application Monitoring
- Laravel Telescope for local debugging
- Sentry for error tracking
- New Relic for performance monitoring
- Custom metrics for business KPIs

### Log Channels
- `daily` - Daily rotating logs
- `slack` - Critical errors to Slack
- `sentry` - All errors to Sentry
- `database` - Activity logs to database

### Metrics to Track
- API response times
- Database query performance
- Cache hit rates
- Queue processing times
- Payment success rates
- User engagement metrics