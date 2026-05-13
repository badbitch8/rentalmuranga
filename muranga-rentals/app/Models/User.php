<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'id_number',
        'profile_photo',
        'bio',
        'address',
        'city',
        'county',
        'is_verified',
        'verification_documents',
        'two_factor_enabled',
        'two_factor_secret',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'verification_documents' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if user is a landlord
     */
    public function isLandlord(): bool
    {
        return $this->role === 'landlord';
    }

    /**
     * Check if user is a tenant
     */
    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Properties owned by the landlord
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    /**
     * Bookings made by the tenant
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    /**
     * Reviews written by the user
     */
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Reviews received by the landlord
     */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'landlord_id');
    }

    /**
     * Maintenance requests created by the user
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'tenant_id');
    }

    /**
     * Messages sent by the user
     */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by the user
     */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Notifications for the user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Saved searches by the user
     */
    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    /**
     * Favorite properties
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Property views by the user
     */
    public function propertyViews()
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Disputes initiated by the user
     */
    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'complainant_id');
    }

    /**
     * Referrals made by the user
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Activity logs for the user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get average rating as a landlord
     */
    public function averageRating()
    {
        return $this->reviewsReceived()->avg('overall_rating');
    }

    /**
     * Get total properties count
     */
    public function totalProperties()
    {
        return $this->properties()->count();
    }

    /**
     * Get active properties count
     */
    public function activeProperties()
    {
        return $this->properties()->where('status', 'approved')->count();
    }
}
