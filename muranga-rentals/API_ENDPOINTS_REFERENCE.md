# 📡 Murang'a Rentals - API Endpoints Reference

Complete reference guide for all 60+ API endpoints.

**Base URL:** `http://127.0.0.1:8000/api/v1`

---

## 🔓 Public Endpoints (No Authentication Required)

### **Authentication**

#### 1. Register User
```
POST /register
```
**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone_number": "254712345678",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "tenant"
}
```
**Response:** User object + authentication token

#### 2. Login
```
POST /login
```
**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```
**Response:** User object + authentication token

---

### **Properties (Public)**

#### 3. List All Properties
```
GET /properties
```
**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `min_price` - Minimum price
- `max_price` - Maximum price
- `bedrooms` - Number of bedrooms
- `type` - Property type
- `location` - Location filter
- `max_distance` - Max distance from MUT (km)
- `amenities` - Comma-separated amenities

**Example:**
```
GET /properties?min_price=5000&max_price=10000&bedrooms=1&max_distance=1
```

#### 4. View Single Property
```
GET /properties/{id}
```
**Response:** Property details with images, landlord info, and statistics

#### 5. Search Properties
```
GET /properties/search?keyword=student&location=kiharu
```

---

### **M-Pesa Callback (Safaricom)**

#### 6. M-Pesa Payment Callback
```
POST /payments/mpesa/callback
```
**Note:** This endpoint is called by Safaricom's servers

---

## 🔐 Protected Endpoints (Requires Authentication)

**Authentication Header Required:**
```
Authorization: Bearer {your_token_here}
```

---

## 👤 User Management

#### 7. Logout
```
POST /logout
```

#### 8. Get Current User
```
GET /me
```

#### 9. Update Profile
```
PUT /profile
```
**Body (multipart/form-data):**
```json
{
  "name": "John Doe Updated",
  "phone_number": "254712345678",
  "bio": "Updated bio",
  "profile_image": [file]
}
```

#### 10. Change Password
```
POST /change-password
```
**Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword",
  "new_password_confirmation": "newpassword"
}
```

---

## 🏠 Property Management

#### 11. Create Property (Landlord Only)
```
POST /properties
```
**Body:**
```json
{
  "name": "Student Apartments",
  "type": "1_bedroom",
  "description": "Modern apartment near MUT",
  "location": "Kiharu, Murang'a",
  "address": "Off Murang'a-Sagana Road",
  "latitude": -0.7167,
  "longitude": 37.1500,
  "distance_to_mut": 0.5,
  "price": 8000,
  "bedrooms": 1,
  "bathrooms": 1,
  "square_feet": 450,
  "amenities": ["wifi", "water", "parking"],
  "utilities_included": ["water"],
  "rules": "No smoking, No pets",
  "available_from": "2026-06-01"
}
```

#### 12. Update Property
```
PUT /properties/{id}
```

#### 13. Delete Property
```
DELETE /properties/{id}
```

#### 14. Upload Property Images
```
POST /properties/{id}/images
```
**Body (multipart/form-data):**
```
images[]: [file1]
images[]: [file2]
images[]: [file3]
```
**Max:** 10 images per property

#### 15. Delete Property Image
```
DELETE /properties/{propertyId}/images/{imageId}
```

#### 16. Toggle Favorite
```
POST /properties/{id}/favorite
```

#### 17. Get My Properties (Landlord)
```
GET /my-properties
```

#### 18. Get My Favorites
```
GET /favorites
```

---

## 📅 Booking Management

#### 19. List Bookings
```
GET /bookings
```
**Query Parameters:**
- `status` - Filter by status (pending, confirmed, active, completed, cancelled)
- `property_id` - Filter by property

#### 20. View Booking
```
GET /bookings/{id}
```

#### 21. Create Booking
```
POST /bookings
```
**Body:**
```json
{
  "property_id": 1,
  "check_in_date": "2026-06-01",
  "check_out_date": "2026-12-01",
  "duration_months": 6,
  "message": "I'm interested in this property"
}
```

#### 22. Update Booking
```
PUT /bookings/{id}
```
**Note:** Only pending bookings can be updated

#### 23. Confirm Booking (Landlord Only)
```
POST /bookings/{id}/confirm
```

#### 24. Cancel Booking
```
POST /bookings/{id}/cancel
```
**Body:**
```json
{
  "cancellation_reason": "Found alternative accommodation"
}
```

#### 25. Sign Contract
```
POST /bookings/{id}/sign
```
**Body:**
```json
{
  "signature": "data:image/png;base64,..."
}
```

---

## 💳 Payment Management

#### 26. List Payments
```
GET /payments
```
**Query Parameters:**
- `status` - Filter by status
- `payment_method` - Filter by method
- `from_date` - Start date
- `to_date` - End date

#### 27. View Payment
```
GET /payments/{id}
```

#### 28. Initiate M-Pesa Payment
```
POST /payments/mpesa/initiate
```
**Body:**
```json
{
  "booking_id": 1,
  "phone_number": "254712345678",
  "amount": 8000,
  "payment_type": "deposit"
}
```
**Payment Types:** deposit, rent, full_payment

#### 29. Check Payment Status
```
GET /payments/{id}/status
```

#### 30. Get Payment Statistics (Landlord)
```
GET /payments/stats
```

#### 31. Generate Receipt
```
GET /payments/{id}/receipt
```

---

## ⭐ Review Management

#### 32. Get Property Reviews
```
GET /properties/{propertyId}/reviews
```
**Query Parameters:**
- `min_rating` - Minimum rating filter
- `sort_by` - Sort option (recent, highest, lowest, helpful)

#### 33. Get My Reviews
```
GET /my-reviews
```

#### 34. Get Landlord Reviews (Landlord Only)
```
GET /landlord/reviews
```
**Query Parameters:**
- `status` - Filter by status
- `property_id` - Filter by property

#### 35. Create Review
```
POST /reviews
```
**Body:**
```json
{
  "property_id": 1,
  "booking_id": 1,
  "rating": 5,
  "cleanliness_rating": 5,
  "communication_rating": 5,
  "location_rating": 4,
  "value_rating": 5,
  "amenities_rating": 4,
  "comment": "Great place for students!",
  "pros": "Clean, close to campus, good landlord",
  "cons": "Slightly expensive"
}
```

#### 36. Update Review
```
PUT /reviews/{id}
```
**Note:** Can only edit within 7 days

#### 37. Delete Review
```
DELETE /reviews/{id}
```

#### 38. Respond to Review (Landlord Only)
```
POST /reviews/{id}/respond
```
**Body:**
```json
{
  "response": "Thank you for your feedback!"
}
```

#### 39. Mark Review as Helpful
```
POST /reviews/{id}/helpful
```

---

## 🔧 Maintenance Requests

#### 40. List Maintenance Requests
```
GET /maintenance-requests
```
**Query Parameters:**
- `status` - Filter by status
- `priority` - Filter by priority
- `property_id` - Filter by property
- `category` - Filter by category

#### 41. View Maintenance Request
```
GET /maintenance-requests/{id}
```

#### 42. Create Maintenance Request
```
POST /maintenance-requests
```
**Body (multipart/form-data):**
```json
{
  "property_id": 1,
  "booking_id": 1,
  "category": "plumbing",
  "priority": "high",
  "title": "Leaking pipe in bathroom",
  "description": "The bathroom sink pipe is leaking",
  "preferred_date": "2026-05-10",
  "preferred_time": "10:00 AM",
  "images[]": [file1, file2]
}
```
**Categories:** plumbing, electrical, appliance, structural, pest_control, hvac, other

#### 43. Update Request Status (Landlord Only)
```
PUT /maintenance-requests/{id}/status
```
**Body:**
```json
{
  "status": "in_progress",
  "notes": "Plumber scheduled for tomorrow",
  "estimated_cost": 2000,
  "scheduled_date": "2026-05-11",
  "contractor_name": "John Plumber",
  "contractor_phone": "254700000000"
}
```

#### 44. Add Actual Cost (Landlord Only)
```
POST /maintenance-requests/{id}/cost
```
**Body (multipart/form-data):**
```json
{
  "actual_cost": 1800,
  "cost_breakdown": "Parts: 1000, Labor: 800",
  "receipt_image": [file]
}
```

#### 45. Cancel Request
```
POST /maintenance-requests/{id}/cancel
```
**Body:**
```json
{
  "reason": "Issue resolved on its own"
}
```

#### 46. Get Statistics
```
GET /maintenance-requests/stats
```

---

## 💬 Messaging System

#### 47. Get All Conversations
```
GET /messages
```

#### 48. Get Conversation with User
```
GET /messages/conversation/{userId}
```

#### 49. Send Message
```
POST /messages
```
**Body (multipart/form-data):**
```json
{
  "receiver_id": 2,
  "message": "Hello, I'm interested in your property",
  "property_id": 1,
  "booking_id": null,
  "attachment": [file]
}
```

#### 50. Mark Message as Read
```
POST /messages/{id}/read
```

#### 51. Mark All Messages as Read
```
POST /messages/{userId}/read-all
```

#### 52. Delete Message
```
DELETE /messages/{id}
```

#### 53. Search Messages
```
GET /messages/search?query=property&user_id=2
```

#### 54. Get Message Statistics
```
GET /messages/stats
```

#### 55. Get Unread Count
```
GET /messages/unread-count
```

#### 56. Block User
```
POST /messages/block/{userId}
```

#### 57. Unblock User
```
POST /messages/unblock/{userId}
```

#### 58. Get Blocked Users
```
GET /messages/blocked-users
```

---

## 🔔 Notifications

#### 59. List Notifications
```
GET /notifications
```
**Query Parameters:**
- `is_read` - Filter by read status
- `type` - Filter by type
- `from_date` - Start date
- `to_date` - End date

#### 60. View Notification
```
GET /notifications/{id}
```

#### 61. Mark as Read
```
POST /notifications/{id}/read
```

#### 62. Mark All as Read
```
POST /notifications/read-all
```

#### 63. Delete Notification
```
DELETE /notifications/{id}
```

#### 64. Delete All Read
```
DELETE /notifications/delete-read
```

#### 65. Get Unread Count
```
GET /notifications/unread-count
```

#### 66. Get Statistics
```
GET /notifications/stats
```

#### 67. Get Grouped by Date
```
GET /notifications/grouped
```

#### 68. Mark as Actioned
```
POST /notifications/{id}/actioned
```

#### 69. Get Preferences
```
GET /notifications/preferences
```

#### 70. Update Preferences
```
PUT /notifications/preferences
```
**Body:**
```json
{
  "email_notifications": true,
  "sms_notifications": true,
  "push_notifications": true,
  "notification_types": ["booking", "payment", "review", "maintenance", "message"]
}
```

#### 71. Send Test Notification
```
POST /notifications/test
```

#### 72. Get Notification Types
```
GET /notifications/types
```

---

## 👨‍💼 Admin Endpoints

**Requires Admin Role**

#### 73. Approve Review
```
POST /admin/reviews/{id}/approve
```

#### 74. Reject Review
```
POST /admin/reviews/{id}/reject
```
**Body:**
```json
{
  "reason": "Inappropriate content"
}
```

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error details"]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "first_page_url": "...",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "next_page_url": "...",
    "path": "...",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
  }
}
```

---

## 🔑 HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

---

## 🧪 Testing Examples

### PowerShell Example
```powershell
# Register user
$body = @{
    name = "Test User"
    email = "test@example.com"
    phone_number = "254700000000"
    password = "password123"
    password_confirmation = "password123"
    role = "tenant"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/register" `
    -Method POST -Body $body -ContentType "application/json"

# Save token
$token = $response.data.token

# Get properties (authenticated)
$headers = @{
    "Authorization" = "Bearer $token"
}

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties" `
    -Method GET -Headers $headers
```

### cURL Example
```bash
# Register
curl -X POST http://127.0.0.1:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone_number": "254700000000",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "tenant"
  }'

# Get properties
curl -X GET http://127.0.0.1:8000/api/v1/properties \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📝 Notes

1. **Authentication:** Most endpoints require Bearer token authentication
2. **File Uploads:** Use `multipart/form-data` content type
3. **Pagination:** Most list endpoints support pagination
4. **Filtering:** Use query parameters for filtering
5. **Rate Limiting:** API may have rate limits in production
6. **CORS:** Configured for local development

---

## 🔗 Related Documentation

- **QUICK_START.md** - Get started in 5 minutes
- **API_TESTING_GUIDE.md** - Detailed testing examples
- **POSTMAN_JSON_EXAMPLES.md** - Copy-paste JSON examples
- **TECHNICAL_SPECIFICATION.md** - Complete API documentation

---

**Last Updated:** May 5, 2026  
**API Version:** 1.0  
**Total Endpoints:** 74

---

**Built for Murang'a University Students & Local Professionals** ❤️