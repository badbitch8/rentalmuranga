# Postman JSON Examples - Copy & Paste Ready

## How to Use in Postman

1. Open Postman
2. Create a new request
3. Set the method (POST, GET, etc.)
4. Enter the URL
5. Go to "Body" tab
6. Select "raw"
7. Select "JSON" from dropdown
8. Copy and paste the JSON below
9. Click "Send"

---

## 1. Register Landlord

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/register`  
**Body (JSON):**

```json
{
    "name": "John Landlord",
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

---

## 2. Register Tenant

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/register`  
**Body (JSON):**

```json
{
    "name": "Jane Tenant",
    "email": "jane@example.com",
    "phone": "0723456789",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "tenant",
    "address": "MUT Campus",
    "city": "Murang'a",
    "county": "Murang'a"
}
```

---

## 3. Login

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/login`  
**Body (JSON):**

```json
{
    "email": "john@example.com",
    "password": "Password123!"
}
```

**After login, copy the token from response and use it in next requests!**

---

## 4. Get Current User Profile

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/me`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE` (replace YOUR_TOKEN_HERE with actual token)

**No Body needed**

---

## 5. Create Property (Landlord)

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/properties`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**Body (JSON):**

```json
{
    "title": "Modern 2BR Apartment near MUT",
    "description": "Spacious 2-bedroom apartment located just 1km from Murang'a University. Features modern amenities, secure parking, and 24/7 water supply. Perfect for students or young professionals.",
    "property_type": "two_bedroom",
    "address": "Kiharu Road, Murang'a",
    "city": "Murang'a",
    "county": "Murang'a",
    "latitude": -0.7167,
    "longitude": 37.1500,
    "distance_to_mut": 1.2,
    "bedrooms": 2,
    "bathrooms": 1,
    "square_feet": 800,
    "rent_amount": 15000,
    "deposit_amount": 15000,
    "is_negotiable": true,
    "available_from": "2024-02-01",
    "lease_duration": "monthly",
    "furnishing_status": "semi_furnished",
    "amenities": ["wifi", "parking", "water_24_7", "security", "backup_generator"],
    "rules": ["no_smoking", "no_pets", "no_loud_music"],
    "utilities_included": ["water", "garbage_collection"],
    "parking_available": true,
    "pet_friendly": false
}
```

---

## 6. Get All Properties (Public - No Auth)

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/properties`  
**No Headers needed**  
**No Body needed**

---

## 7. Get Properties with Filters

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/properties?min_price=10000&max_price=20000&bedrooms=2&property_type=two_bedroom&max_distance=2`  
**No Headers needed**  
**No Body needed**

---

## 8. Get Single Property

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/properties/1` (replace 1 with actual property ID)  
**No Headers needed**  
**No Body needed**

---

## 9. Add Property to Favorites

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/properties/1/favorite` (replace 1 with property ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TENANT_TOKEN_HERE`

**No Body needed**

---

## 10. Create Booking (Tenant)

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/bookings`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TENANT_TOKEN_HERE`

**Body (JSON):**

```json
{
    "property_id": 1,
    "start_date": "2024-02-01",
    "end_date": "2024-08-01",
    "notes": "I am a MUT student looking for accommodation near campus. I am responsible and will take good care of the property."
}
```

---

## 11. Get All Bookings

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/bookings`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE`

**No Body needed**

---

## 12. Get Single Booking

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/bookings/1` (replace 1 with booking ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE`

**No Body needed**

---

## 13. Confirm Booking (Landlord Only)

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/bookings/1/confirm` (replace 1 with booking ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**No Body needed**

---

## 14. Sign Contract - Tenant

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/bookings/1/sign` (replace 1 with booking ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TENANT_TOKEN_HERE`

**Body (JSON):**

```json
{
    "signature": "Jane Tenant - Digital Signature - 2024-01-15"
}
```

---

## 15. Sign Contract - Landlord

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/bookings/1/sign` (replace 1 with booking ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**Body (JSON):**

```json
{
    "signature": "John Landlord - Digital Signature - 2024-01-15"
}
```

---

## 16. Cancel Booking

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/bookings/1/cancel` (replace 1 with booking ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE`

**Body (JSON):**

```json
{
    "cancellation_reason": "Found alternative accommodation closer to campus"
}
```

---

## 17. Get My Properties (Landlord)

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/my-properties`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**No Body needed**

---

## 18. Get My Favorites (Tenant)

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/favorites`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TENANT_TOKEN_HERE`

**No Body needed**

---

## 19. Update Profile

**Method:** PUT  
**URL:** `http://localhost:8000/api/v1/profile`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE`

**Body (JSON):**

```json
{
    "name": "John Updated Landlord",
    "phone": "0712345679",
    "bio": "Experienced landlord with 5+ properties in Murang'a County. Committed to providing quality housing for students and professionals.",
    "address": "Murang'a Town Center"
}
```

---

## 20. Change Password

**Method:** POST  
**URL:** `http://localhost:8000/api/v1/change-password`  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_TOKEN_HERE`

**Body (JSON):**

```json
{
    "current_password": "Password123!",
    "new_password": "NewPassword123!",
    "new_password_confirmation": "NewPassword123!"
}
```

---

## 21. Search Properties

**Method:** GET  
**URL:** `http://localhost:8000/api/v1/properties/search?keyword=apartment&city=Murang'a&min_price=10000&max_price=20000`  
**No Headers needed**  
**No Body needed**

---

## 22. Update Property

**Method:** PUT  
**URL:** `http://localhost:8000/api/v1/properties/1` (replace 1 with property ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**Body (JSON):**

```json
{
    "title": "Updated: Modern 2BR Apartment near MUT",
    "description": "Updated description with more details",
    "rent_amount": 16000,
    "is_negotiable": true
}
```

---

## 23. Delete Property

**Method:** DELETE  
**URL:** `http://localhost:8000/api/v1/properties/1` (replace 1 with property ID)  
**Headers:**
- Key: `Authorization`
- Value: `Bearer YOUR_LANDLORD_TOKEN_HERE`

**No Body needed**

---

## Quick Testing Workflow

### Step 1: Register Users
1. Register a landlord (Example #1)
2. Copy the token from response
3. Register a tenant (Example #2)
4. Copy the token from response

### Step 2: Create Property
1. Use landlord token
2. Create property (Example #5)
3. Copy property ID from response

### Step 3: Browse & Favorite
1. Get all properties (Example #6)
2. Use tenant token
3. Add to favorites (Example #9)

### Step 4: Create Booking
1. Use tenant token
2. Create booking (Example #10)
3. Copy booking ID from response

### Step 5: Complete Booking
1. Use landlord token
2. Confirm booking (Example #13)
3. Use tenant token
4. Sign contract (Example #14)
5. Use landlord token
6. Sign contract (Example #15)

---

## Tips for Postman

1. **Save Tokens as Variables:**
   - Go to Environment
   - Create variables: `landlord_token`, `tenant_token`
   - Use `{{landlord_token}}` in Authorization header

2. **Create a Collection:**
   - Save all requests in one collection
   - Easy to reuse and share

3. **Use Tests Tab:**
   - Auto-save tokens from responses
   - Validate response structure

4. **Check Response:**
   - Look for `"success": true`
   - Check `data` object for results
   - Note any error messages

---

**All JSON examples are ready to copy and paste into Postman!**