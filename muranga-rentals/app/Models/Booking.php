<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'property_id',
        'tenant_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'deposit_amount',
        'total_amount',
        'status',
        'contract_path',
        'tenant_signature',
        'landlord_signature',
        'signed_at',
        'move_in_date',
        'move_out_date',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'signed_at' => 'datetime',
            'move_in_date' => 'date',
            'move_out_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the property for the booking
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the tenant for the booking
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the payments for the booking
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the maintenance requests for the booking
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Scope a query to only include pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include active bookings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if booking is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if contract is signed
     */
    public function isSigned(): bool
    {
        return !is_null($this->tenant_signature) && 
               !is_null($this->landlord_signature) && 
               !is_null($this->signed_at);
    }

    /**
     * Get total paid amount
     */
    public function totalPaid()
    {
        return $this->payments()
                    ->where('status', 'completed')
                    ->sum('amount');
    }

    /**
     * Get remaining balance
     */
    public function remainingBalance()
    {
        return $this->total_amount - $this->totalPaid();
    }

    /**
     * Check if fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->remainingBalance() <= 0;
    }

    /**
     * Get duration in months
     */
    public function getDurationInMonths()
    {
        return $this->start_date->diffInMonths($this->end_date);
    }

    /**
     * Get formatted monthly rent
     */
    public function getFormattedMonthlyRentAttribute()
    {
        return 'KES ' . number_format($this->monthly_rent, 2);
    }

    /**
     * Get formatted deposit amount
     */
    public function getFormattedDepositAttribute()
    {
        return 'KES ' . number_format($this->deposit_amount, 2);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return 'KES ' . number_format($this->total_amount, 2);
    }

    /**
     * Get contract URL
     */
    public function getContractUrlAttribute()
    {
        return $this->contract_path ? asset('storage/' . $this->contract_path) : null;
    }
}

// Made with Bob
