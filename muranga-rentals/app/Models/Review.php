<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'property_id',
        'booking_id',
        'reviewer_id',
        'landlord_id',
        'overall_rating',
        'cleanliness_rating',
        'communication_rating',
        'value_rating',
        'location_rating',
        'amenities_rating',
        'title',
        'comment',
        'landlord_response',
        'responded_at',
        'is_verified',
        'helpful_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:1',
            'cleanliness_rating' => 'integer',
            'communication_rating' => 'integer',
            'value_rating' => 'integer',
            'location_rating' => 'integer',
            'amenities_rating' => 'integer',
            'responded_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the property being reviewed
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the booking associated with the review
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the landlord being reviewed
     */
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /**
     * Scope a query to only include verified reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to filter by minimum rating
     */
    public function scopeMinRating($query, $rating)
    {
        return $query->where('overall_rating', '>=', $rating);
    }

    /**
     * Scope a query to order by most helpful
     */
    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    /**
     * Scope a query to order by most recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if review has landlord response
     */
    public function hasResponse(): bool
    {
        return !is_null($this->landlord_response);
    }

    /**
     * Check if review is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified === true;
    }

    /**
     * Get rating stars as HTML
     */
    public function getRatingStarsAttribute()
    {
        $fullStars = floor($this->overall_rating);
        $halfStar = ($this->overall_rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        return [
            'full' => $fullStars,
            'half' => $halfStar ? 1 : 0,
            'empty' => $emptyStars,
        ];
    }

    /**
     * Increment helpful count
     */
    public function incrementHelpful()
    {
        $this->increment('helpful_count');
    }
}

// Made with Bob
