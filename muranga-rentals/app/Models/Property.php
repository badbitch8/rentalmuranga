<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'landlord_id',
        'title',
        'description',
        'property_type',
        'address',
        'city',
        'county',
        'latitude',
        'longitude',
        'distance_to_mut',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'rent_amount',
        'deposit_amount',
        'is_negotiable',
        'available_from',
        'lease_duration',
        'furnishing_status',
        'amenities',
        'rules',
        'utilities_included',
        'parking_available',
        'pet_friendly',
        'status',
        'rejection_reason',
        'views_count',
        'favorites_count',
        'verified_at',
        'featured_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'distance_to_mut' => 'decimal:2',
            'rent_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'available_from' => 'date',
            'amenities' => 'array',
            'rules' => 'array',
            'utilities_included' => 'array',
            'parking_available' => 'boolean',
            'pet_friendly' => 'boolean',
            'verified_at' => 'datetime',
            'featured_until' => 'datetime',
        ];
    }

    /**
     * Get the landlord that owns the property
     */
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /**
     * Get the images for the property
     */
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Get the primary image for the property
     */
    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    /**
     * Get the bookings for the property
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews for the property
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the maintenance requests for the property
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get the favorites for the property
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the views for the property
     */
    public function views()
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Scope a query to only include approved properties
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include available properties
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'approved')
                     ->where('available_from', '<=', now());
    }

    /**
     * Scope a query to only include featured properties
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured_until', '>', now());
    }

    /**
     * Scope a query to filter by property type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope a query to filter by price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('rent_amount', [$min, $max]);
    }

    /**
     * Scope a query to filter by bedrooms
     */
    public function scopeBedrooms($query, $count)
    {
        return $query->where('bedrooms', '>=', $count);
    }

    /**
     * Scope a query to filter by distance to MUT
     */
    public function scopeNearMUT($query, $maxDistance)
    {
        return $query->where('distance_to_mut', '<=', $maxDistance);
    }

    /**
     * Scope a query to search by keyword
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('address', 'like', "%{$keyword}%");
        });
    }

    /**
     * Get average rating
     */
    public function averageRating()
    {
        return $this->reviews()->avg('overall_rating');
    }

    /**
     * Get total reviews count
     */
    public function totalReviews()
    {
        return $this->reviews()->count();
    }

    /**
     * Check if property is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'approved' && 
               $this->available_from <= now();
    }

    /**
     * Check if property is featured
     */
    public function isFeatured(): bool
    {
        return $this->featured_until && $this->featured_until > now();
    }

    /**
     * Check if property is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment favorites count
     */
    public function incrementFavorites()
    {
        $this->increment('favorites_count');
    }

    /**
     * Decrement favorites count
     */
    public function decrementFavorites()
    {
        $this->decrement('favorites_count');
    }

    /**
     * Get formatted rent amount
     */
    public function getFormattedRentAttribute()
    {
        return 'KES ' . number_format($this->rent_amount, 2);
    }

    /**
     * Get formatted deposit amount
     */
    public function getFormattedDepositAttribute()
    {
        return 'KES ' . number_format($this->deposit_amount, 2);
    }

    /**
     * Get distance to MUT in kilometers
     */
    public function getDistanceToMutKmAttribute()
    {
        return number_format($this->distance_to_mut, 1) . ' km';
    }
}

// Made with Bob
