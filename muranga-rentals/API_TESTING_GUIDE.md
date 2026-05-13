# API Testing Guide - Murang'a Rentals

## Prerequisites
- XAMPP running (Apache & MySQL)
- Laravel server running: `php artisan serve`
- API available at: http://localhost:8000

## Testing Tools Options

### Option 1: Postman (Recommended)
1. Download Postman: https://www.postman.com/downloads/
2. Import the API collection (see examples below)
3. Test endpoints visually

### Option 2: Thunder Client (VS Code Extension)
1. Install Thunder Client extension in VS Code
2. Create requests directly in VS Code
3. Easy to use and lightweight

### Option 3: PowerShell (Command Line)

## PowerShell Testing Commands

### 1. Register a Landlord

```powershell
$body = @{
    name = "John Landlord"
    email = "john@example.com"
    phone = "0712345678"
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "landlord"
    address = "Murang'a Town"
    city = "Murang'a"
    county = "Murang'a"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/register" -Method Post -Body $body -ContentType "application/json"
$response | ConvertTo-Json -Depth 10

# Save the token
$landlordToken = $response.data.token
Write-Host "Landlord Token: $landlordToken"
```

### 2. Register a Tenant

```powershell
$body = @{
    name = "Jane Tenant"
    email = "jane@example.com"
    phone = "0723456789"
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "tenant"
    address = "MUT Campus"
    city = "Murang'a"
    county = "Murang'a"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/register" -Method Post -Body $body -ContentType "application/json"
$response | ConvertTo-Json -Depth 10

# Save the token
$tenantToken = $response.data.token
Write-Host "Tenant Token: $tenantToken"
```

### 3. Login

```powershell
$body = @{
    email = "john@example.com"
    password = "Password123!"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/login" -Method Post -Body $body -ContentType "application/json"
$response | ConvertTo-Json -Depth 10

$token = $response.data.token
Write-Host "Token: $token"
```

### 4. Get Current User Profile

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
}

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/me" -Method Get -Headers $headers
$response | ConvertTo-Json -Depth 10
```

### 5. Create a Property (Landlord)

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

$body = @{
    title = "Modern 2BR Apartment near MUT"
    description = "Spacious 2-bedroom apartment located just 1km from Murang'a University. Features modern amenities, secure parking, and 24/7 water supply."
    property_type = "two_bedroom"
    address = "Kiharu Road, Murang'a"
    city = "Murang'a"
    county = "Murang'a"
    latitude = -0.7167
    longitude = 37.1500
    distance_to_mut = 1.2
    bedrooms = 2
    bathrooms = 1
    square_feet = 800
    rent_amount = 15000
    deposit_amount = 15000
    is_negotiable = $true
    available_from = "2024-02-01"
    lease_duration = "monthly"
    furnishing_status = "semi_furnished"
    amenities = @("wifi", "parking", "water_24_7", "security")
    rules = @("no_smoking", "no_pets")
    utilities_included = @("water", "garbage_collection")
    parking_available = $true
    pet_friendly = $false
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/properties" -Method Post -Headers $headers -Body $body
$response | ConvertTo-Json -Depth 10

# Save property ID
$propertyId = $response.data.id
Write-Host "Property ID: $propertyId"
```

### 6. Get All Properties (Public)

```powershell
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/properties" -Method Get
$response | ConvertTo-Json -Depth 10
```

### 7. Get Properties with Filters

```powershell
$params = @{
    min_price = 10000
    max_price = 20000
    bedrooms = 2
    property_type = "two_bedroom"
    max_distance = 2
    per_page = 10
}

$queryString = ($params.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"
$uri = "http://localhost:8000/api/v1/properties?$queryString"

$response = Invoke-RestMethod -Uri $uri -Method Get
$response | ConvertTo-Json -Depth 10
```

### 8. Get Single Property

```powershell
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/properties/$propertyId" -Method Get
$response | ConvertTo-Json -Depth 10
```

### 9. Add Property to Favorites (Tenant)

```powershell
$headers = @{
    "Authorization" = "Bearer $tenantToken"
    "Accept" = "application/json"
}

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/properties/$propertyId/favorite" -Method Post -Headers $headers
$response | ConvertTo-Json -Depth 10
```

### 10. Create a Booking (Tenant)

```powershell
$headers = @{
    "Authorization" = "Bearer $tenantToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

$body = @{
    property_id = $propertyId
    start_date = "2024-02-01"
    end_date = "2024-08-01"
    notes = "Looking forward to moving in. I'm a MUT student."
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/bookings" -Method Post -Headers $headers -Body $body
$response | ConvertTo-Json -Depth 10

# Save booking ID
$bookingId = $response.data.id
Write-Host "Booking ID: $bookingId"
```

### 11. Get All Bookings

```powershell
$headers = @{
    "Authorization" = "Bearer $tenantToken"
    "Accept" = "application/json"
}

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/bookings" -Method Get -Headers $headers
$response | ConvertTo-Json -Depth 10
```

### 12. Confirm Booking (Landlord)

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
}

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/bookings/$bookingId/confirm" -Method Post -Headers $headers
$response | ConvertTo-Json -Depth 10
```

### 13. Sign Contract (Tenant)

```powershell
$headers = @{
    "Authorization" = "Bearer $tenantToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

$body = @{
    signature = "Jane Tenant - Digital Signature - " + (Get-Date).ToString()
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/bookings/$bookingId/sign" -Method Post -Headers $headers -Body $body
$response | ConvertTo-Json -Depth 10
```

### 14. Sign Contract (Landlord)

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

$body = @{
    signature = "John Landlord - Digital Signature - " + (Get-Date).ToString()
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/bookings/$bookingId/sign" -Method Post -Headers $headers -Body $body
$response | ConvertTo-Json -Depth 10
```

### 15. Get My Properties (Landlord)

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
}

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/my-properties" -Method Get -Headers $headers
$response | ConvertTo-Json -Depth 10
```

### 16. Update Profile

```powershell
$headers = @{
    "Authorization" = "Bearer $landlordToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

$body = @{
    name = "John Updated Landlord"
    bio = "Experienced landlord with 5+ properties in Murang'a"
    phone = "0712345679"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/profile" -Method Put -Headers $headers -Body $body
$response | ConvertTo-Json -Depth 10
```

## Complete Test Script

Save this as `test-api.ps1`:

```powershell
# Murang'a Rentals API Test Script

Write-Host "=== Murang'a Rentals API Testing ===" -ForegroundColor Green
Write-Host ""

# 1. Register Landlord
Write-Host "1. Registering Landlord..." -ForegroundColor Yellow
$landlordBody = @{
    name = "John Landlord"
    email = "john@example.com"
    phone = "0712345678"
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "landlord"
} | ConvertTo-Json

try {
    $landlordResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/register" -Method Post -Body $landlordBody -ContentType "application/json"
    $landlordToken = $landlordResponse.data.token
    Write-Host "✓ Landlord registered successfully" -ForegroundColor Green
    Write-Host "Token: $landlordToken" -ForegroundColor Cyan
} catch {
    Write-Host "✗ Failed to register landlord: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# 2. Register Tenant
Write-Host "2. Registering Tenant..." -ForegroundColor Yellow
$tenantBody = @{
    name = "Jane Tenant"
    email = "jane@example.com"
    phone = "0723456789"
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "tenant"
} | ConvertTo-Json

try {
    $tenantResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/register" -Method Post -Body $tenantBody -ContentType "application/json"
    $tenantToken = $tenantResponse.data.token
    Write-Host "✓ Tenant registered successfully" -ForegroundColor Green
    Write-Host "Token: $tenantToken" -ForegroundColor Cyan
} catch {
    Write-Host "✗ Failed to register tenant: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Testing Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "Landlord Token: $landlordToken" -ForegroundColor Cyan
Write-Host "Tenant Token: $tenantToken" -ForegroundColor Cyan
```

Run with: `.\test-api.ps1`

## Using Postman (Easier Method)

### Import Collection:
1. Open Postman
2. Click "Import"
3. Create new requests with these details:

**Register:**
- Method: POST
- URL: http://localhost:8000/api/v1/register
- Body (raw JSON):
```json
{
    "name": "John Landlord",
    "email": "john@example.com",
    "phone": "0712345678",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "landlord"
}
```

**Create Property:**
- Method: POST
- URL: http://localhost:8000/api/v1/properties
- Headers: 
  - Authorization: Bearer YOUR_TOKEN_HERE
  - Content-Type: application/json
- Body (raw JSON):
```json
{
    "title": "Modern 2BR Apartment near MUT",
    "description": "Spacious apartment",
    "property_type": "two_bedroom",
    "address": "Kiharu Road",
    "city": "Murang'a",
    "county": "Murang'a",
    "bedrooms": 2,
    "bathrooms": 1,
    "rent_amount": 15000,
    "deposit_amount": 15000,
    "available_from": "2024-02-01",
    "lease_duration": "monthly",
    "furnishing_status": "semi_furnished",
    "parking_available": true,
    "pet_friendly": false
}
```

## Troubleshooting

### Error: Connection Refused
- Ensure Laravel server is running: `php artisan serve`
- Check XAMPP MySQL is running

### Error: 401 Unauthorized
- Check your token is correct
- Token format: `Bearer YOUR_TOKEN_HERE`

### Error: 422 Validation Error
- Check all required fields are provided
- Verify data types match requirements

### Error: 500 Internal Server Error
- Check `storage/logs/laravel.log` for details
- Ensure database migrations ran successfully

## Next Steps

After testing these endpoints, you can:
1. Test payment integration (coming next)
2. Test review system
3. Test maintenance requests
4. Test messaging system

---

**Tip:** Use Postman or Thunder Client for easier API testing instead of PowerShell commands!