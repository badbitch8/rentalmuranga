# Implementation Guide - Murang'a Rental Marketplace

## Phase 1: Foundation Setup (Weeks 1-3)

### Week 1: Project Initialization

#### Day 1-2: Environment Setup
```bash
# Install Laravel
composer create-project laravel/laravel muranga-rentals
cd muranga-rentals

# Install required packages
composer require laravel/sanctum
composer require laravel/telescope
composer require spatie/laravel-permission
composer require intervention/image
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
composer require laravel/scout
composer require meilisearch/meilisearch-php

# Install Vue.js and dependencies
npm install vue@next
npm install @vitejs/plugin-vue
npm install pinia
npm install vue-router
npm install axios
npm install tailwindcss
npm install @headlessui/vue
npm install @heroicons/vue

# Development tools
composer require --dev laravel/pint
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
npm install --save-dev @vue/test-utils
```

#### Day 3-4: Project Structure Setup
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   ├── Property/
│   │   │   ├── Booking/
│   │   │   ├── Payment/
│   │   │   ├── Review/
│   │   │   ├── Maintenance/
│   │   │   ├── Message/
│   │   │   └── Admin/
│   │   └── Web/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Services/
│   ├── Payment/
│   ├── Notification/
│   ├── AI/
│   └── Contract/
├── Repositories/
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
└── Traits/

resources/
├── js/
│   ├── components/
│   │   ├── common/
│   │   ├── property/
│   │   ├── booking/
│   │   ├── dashboard/
│   │   └── admin/
│   ├── views/
│   ├── router/
│   ├── stores/
│   ├── composables/
│   └── utils/
└── css/
```

#### Day 5-7: Database Setup
```bash
# Create migrations
php artisan make:migration create_users_table
php artisan make:migration create_properties_table
php artisan make:migration create_property_images_table
php artisan make:migration create_bookings_table
php artisan make:migration create_payments_table
php artisan make:migration create_reviews_table
php artisan make:migration create_maintenance_requests_table
php artisan make:migration create_messages_table
php artisan make:migration create_notifications_table
php artisan make:migration create_saved_searches_table
php artisan make:migration create_favorites_table
php artisan make:migration create_property_views_table
php artisan make:migration create_disputes_table
php artisan make:migration create_referrals_table
php artisan make:migration create_activity_logs_table

# Run migrations
php artisan migrate

# Create seeders
php artisan make:seeder UserSeeder
php artisan make:seeder PropertySeeder
php artisan make:seeder RoleAndPermissionSeeder

# Seed database
php artisan db:seed
```

### Week 2: Authentication & User Management

#### Models Setup
```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'id_number', 'profile_photo', 'bio',
        'verification_status', 'verification_documents',
        'two_factor_secret', 'two_factor_enabled'
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verification_documents' => 'array',
        'two_factor_enabled' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'tenant_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Scopes
    public function scopeLandlords($query)
    {
        return $query->where('role', 'landlord');
    }

    public function scopeTenants($query)
    {
        return $query->where('role', 'tenant');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }
}
```

#### Authentication Controllers
```php
// app/Http/Controllers/Api/Auth/RegisterController.php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'tenant',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Registration successful',
        ], 201);
    }
}
```

### Week 3: Core Models & Relationships

#### Property Model
```php
// app/Models/Property.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Property extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'landlord_id', 'title', 'slug', 'description',
        'property_type', 'bedrooms', 'bathrooms', 'square_feet',
        'rent_amount', 'deposit_amount', 'address',
        'latitude', 'longitude', 'county', 'sub_county', 'ward',
        'distance_to_mut', 'amenities', 'rules',
        'furnishing_status', 'availability_status',
        'available_from', 'verification_status',
        'virtual_tour_url', 'video_url'
    ];

    protected $casts = [
        'amenities' => 'array',
        'rules' => 'array',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_to_mut' => 'decimal:2',
        'available_from' => 'date',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function views()
    {
        return $this->hasMany(PropertyView::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeNearMUT($query, $maxDistance = 5)
    {
        return $query->where('distance_to_mut', '<=', $maxDistance);
    }

    public function scopeInBudget($query, $minRent, $maxRent)
    {
        return $query->whereBetween('rent_amount', [$minRent, $maxRent]);
    }

    // Searchable configuration
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'property_type' => $this->property_type,
            'rent_amount' => $this->rent_amount,
        ];
    }
}
```

## Phase 2: Core Features (Weeks 4-7)

### Week 4: Property Listing System

#### Property Controller
```php
// app/Http/Controllers/Api/Property/PropertyController.php
<?php

namespace App\Http\Controllers\Api\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\Property\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $propertyService
    ) {}

    public function index(Request $request)
    {
        $properties = $this->propertyService->search($request->all());

        return PropertyResource::collection($properties);
    }

    public function store(StorePropertyRequest $request)
    {
        $property = $this->propertyService->create($request->validated());

        return new PropertyResource($property);
    }

    public function show(Property $property)
    {
        $this->propertyService->recordView($property);

        return new PropertyResource($property->load([
            'landlord',
            'images',
            'reviews.tenant'
        ]));
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        $property = $this->propertyService->update($property, $request->validated());

        return new PropertyResource($property);
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);
    }
}
```

#### Property Service
```php
// app/Services/Property/PropertyService.php
<?php

namespace App\Services\Property;

use App\Models\Property;
use App\Models\PropertyView;
use Illuminate\Support\Str;

class PropertyService
{
    public function search(array $filters)
    {
        $query = Property::query()
            ->with(['landlord', 'images'])
            ->verified()
            ->available();

        // Apply filters
        if (isset($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (isset($filters['min_rent']) && isset($filters['max_rent'])) {
            $query->inBudget($filters['min_rent'], $filters['max_rent']);
        }

        if (isset($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (isset($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        if (isset($filters['distance_to_mut'])) {
            $query->nearMUT($filters['distance_to_mut']);
        }

        if (isset($filters['amenities'])) {
            foreach ($filters['amenities'] as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Location-based search
        if (isset($filters['latitude']) && isset($filters['longitude'])) {
            $radius = $filters['radius'] ?? 5; // Default 5km
            $query->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) + sin(radians(?)) *
                sin(radians(latitude)))) AS distance
            ", [$filters['latitude'], $filters['longitude'], $filters['latitude']])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }

        // Text search
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhere('address', 'like', "%{$filters['search']}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'newest';
        switch ($sortBy) {
            case 'rent_asc':
                $query->orderBy('rent_amount', 'asc');
                break;
            case 'rent_desc':
                $query->orderBy('rent_amount', 'desc');
                break;
            case 'distance':
                $query->orderBy('distance_to_mut', 'asc');
                break;
            default:
                $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        $data['landlord_id'] = auth()->id();

        return Property::create($data);
    }

    public function update(Property $property, array $data)
    {
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }

        $property->update($data);

        return $property->fresh();
    }

    public function recordView(Property $property)
    {
        PropertyView::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $property->increment('views_count');
    }
}
```

### Week 5-6: Image Upload & Management

#### Image Upload Controller
```php
// app/Http/Controllers/Api/Property/PropertyImageController.php
<?php

namespace App\Http\Controllers\Api\Property;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\Image\ImageService;
use Illuminate\Http\Request;

class PropertyImageController extends Controller
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function store(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        $images = [];

        foreach ($request->file('images') as $index => $image) {
            $urls = $this->imageService->uploadPropertyImage($image, $property->id);

            $propertyImage = PropertyImage::create([
                'property_id' => $property->id,
                'image_url' => $urls['original'],
                'thumbnail_url' => $urls['thumbnail'],
                'is_primary' => $index === 0 && $property->images()->count() === 0,
                'display_order' => $property->images()->count() + $index,
            ]);

            $images[] = $propertyImage;
        }

        return response()->json([
            'success' => true,
            'data' => $images,
            'message' => 'Images uploaded successfully'
        ]);
    }

    public function destroy(Property $property, PropertyImage $image)
    {
        $this->authorize('update', $property);

        $this->imageService->deletePropertyImage($image);

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
```

#### Image Service
```php
// app/Services/Image/ImageService.php
<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageService
{
    public function uploadPropertyImage($file, $propertyId)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = "properties/{$propertyId}/images";

        // Upload original
        $originalPath = $file->storeAs($path, $filename, 'public');

        // Create thumbnail
        $thumbnail = Image::make($file)
            ->fit(400, 300)
            ->encode($file->getClientOriginalExtension(), 80);

        $thumbnailPath = "{$path}/thumbnails/{$filename}";
        Storage::disk('public')->put($thumbnailPath, $thumbnail);

        return [
            'original' => Storage::url($originalPath),
            'thumbnail' => Storage::url($thumbnailPath),
        ];
    }

    public function deletePropertyImage($image)
    {
        Storage::disk('public')->delete([
            str_replace('/storage/', '', $image->image_url),
            str_replace('/storage/', '', $image->thumbnail_url),
        ]);
    }
}
```

### Week 7: Search & Filter Implementation

#### Advanced Search Service
```php
// app/Services/Search/SearchService.php
<?php

namespace App\Services\Search;

use App\Models\Property;
use App\Models\SavedSearch;

class SearchService
{
    public function advancedSearch(array $criteria)
    {
        $query = Property::search($criteria['search'] ?? '')
            ->query(function ($builder) use ($criteria) {
                $builder->with(['landlord', 'images'])
                    ->verified()
                    ->available();

                // Apply all filters from PropertyService
                // ... (similar to PropertyService search method)
            });

        return $query->paginate($criteria['per_page'] ?? 20);
    }

    public function saveSearch(array $criteria, $userId)
    {
        return SavedSearch::create([
            'user_id' => $userId,
            'name' => $criteria['name'],
            'search_criteria' => $criteria,
            'email_alerts' => $criteria['email_alerts'] ?? true,
            'alert_frequency' => $criteria['alert_frequency'] ?? 'daily',
        ]);
    }

    public function getSuggestions($userId)
    {
        // AI-powered suggestions based on user behavior
        // This will be implemented in Phase 5
        return [];
    }
}
```

## Phase 3: Booking & Payments (Weeks 8-10)

### Week 8: Booking System

#### Booking Model & Controller
```php
// app/Models/Booking.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'property_id', 'tenant_id', 'landlord_id',
        'booking_reference', 'start_date', 'end_date',
        'lease_duration_months', 'status',
        'rent_amount', 'deposit_amount', 'total_amount',
        'contract_url', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_paid' => 'boolean',
        'first_rent_paid' => 'boolean',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Generate unique booking reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference = 'BK-' . strtoupper(uniqid());
        });
    }
}
```

### Week 9-10: M-Pesa Integration

#### M-Pesa Service
```php
// app/Services/Payment/MpesaService.php
<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->callbackUrl = config('services.mpesa.callback_url');
        $this->baseUrl = config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function getAccessToken()
    {
        return Cache::remember('mpesa_access_token', 3500, function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            return $response->json()['access_token'];
        });
    }

    public function stkPush($phone, $amount, $accountReference, $description)
    {
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $description,
            ]);

        return $response->json();
    }

    public function handleCallback($data)
    {
        // Process M-Pesa callback
        $resultCode = $data['Body']['stkCallback']['ResultCode'];

        if ($resultCode == 0) {
            // Payment successful
            $metadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'];
            
            return [
                'success' => true,
                'amount' => $this->getMetadataValue($metadata, 'Amount'),
                'receipt' => $this->getMetadataValue($metadata, 'MpesaReceiptNumber'),
                'phone' => $this->getMetadataValue($metadata, 'PhoneNumber'),
                'transaction_date' => $this->getMetadataValue($metadata, 'TransactionDate'),
            ];
        }

        return ['success' => false, 'message' => 'Payment failed'];
    }

    private function getMetadataValue($metadata, $name)
    {
        foreach ($metadata as $item) {
            if ($item['Name'] === $name) {
                return $item['Value'];
            }
        }
        return null;
    }
}
```

## Phase 4: Communication (Weeks 11-12)

### Real-time Chat Implementation

```php
// app/Events/MessageSent.php
<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn()
    {
        return new Channel("chat.{$this->message->conversation_id}");
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'sender' => $this->message->sender,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at,
        ];
    }
}
```

## Deployment Checklist

### Pre-deployment
- [ ] All tests passing
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] Assets compiled and optimized
- [ ] API documentation complete
- [ ] Security audit completed

### Deployment Steps
1. Set up cloud infrastructure
2. Configure database and Redis
3. Deploy application code
4. Run migrations
5. Configure CDN
6. Set up SSL certificates
7. Configure monitoring
8. Test all critical paths
9. Enable backups
10. Go live!

### Post-deployment
- [ ] Monitor error rates
- [ ] Check performance metrics
- [ ] Verify payment integration
- [ ] Test notification delivery
- [ ] Monitor user feedback
- [ ] Plan iteration cycle

## Testing Strategy

### Unit Tests Example
```php
// tests/Unit/PropertyServiceTest.php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Property\PropertyService;
use App\Models\Property;
use App\Models\User;

class PropertyServiceTest extends TestCase
{
    public function test_can_create_property()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $this->actingAs($landlord);

        $service = new PropertyService();
        $property = $service->create([
            'title' => 'Test Property',
            'description' => 'Test Description',
            'property_type' => 'apartment',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'rent_amount' => 15000,
            'deposit_amount' => 15000,
            'address' => 'Test Address',
            'latitude' => -0.7893,
            'longitude' => 37.1506,
        ]);

        $this->assertInstanceOf(Property::class, $property);
        $this->assertEquals('Test Property', $property->title);
    }
}
```

### Feature Tests Example
```php
// tests/Feature/PropertyApiTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Property;

class PropertyApiTest extends TestCase
{
    public function test_can_list_properties()
    {
        Property::factory()->count(5)->create();

        $response = $this->getJson('/api/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'rent_amount']
                ]
            ]);
    }

    public function test_landlord_can_create_property()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);

        $response = $this->actingAs($landlord)
            ->postJson('/api/properties', [
                'title' => 'New Property',
                'description' => 'Description',
                'property_type' => 'apartment',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'rent_amount' => 15000,
                'deposit_amount' => 15000,
                'address' => 'Test Address',
                'latitude' => -0.7893,
                'longitude' => 37.1506,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title']]);
    }
}
```

## Performance Optimization Tips

1. **Database Optimization**
   - Use eager loading to prevent N+1 queries
   - Add proper indexes on frequently queried columns
   - Use database query caching for expensive queries

2. **Caching Strategy**
   - Cache property listings for 5 minutes
   - Cache user profiles for 30 minutes
   - Cache search results based on query hash

3. **Frontend Optimization**
   - Implement lazy loading for images
   - Use code splitting for routes
   - Minimize bundle size with tree shaking

4. **API Optimization**
   - Implement pagination for all list endpoints
   - Use API resource transformers
   - Implement rate limiting

## Maintenance & Support

### Regular Tasks
- Daily: Monitor error logs and performance
- Weekly: Review user feedback and bug reports
- Monthly: Security updates and dependency updates
- Quarterly: Performance optimization review

### Backup Strategy
- Automated daily database backups
- Weekly full system backups
- 30-day retention policy
- Regular backup restoration tests

## Conclusion

This implementation guide provides a structured approach to building the Murang'a Rental Marketplace. Follow each phase sequentially, ensuring proper testing and documentation at each step. The modular architecture allows for flexibility and scalability as the platform grows.