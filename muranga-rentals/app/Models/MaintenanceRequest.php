<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'property_id',
        'booking_id',
        'tenant_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'images',
        'estimated_cost',
        'actual_cost',
        'contractor_name',
        'contractor_phone',
        'scheduled_date',
        'completed_date',
        'tenant_notes',
        'landlord_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'scheduled_date' => 'datetime',
            'completed_date' => 'datetime',
        ];
    }

    /**
     * Get the property for the maintenance request
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the booking for the maintenance request
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the tenant who created the request
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Scope a query to only include pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in-progress requests
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to filter by priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to order by priority (high to low)
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if request is urgent
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
        ];

        return $colors[$this->priority] ?? 'gray';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get formatted estimated cost
     */
    public function getFormattedEstimatedCostAttribute()
    {
        return $this->estimated_cost ? 'KES ' . number_format($this->estimated_cost, 2) : 'N/A';
    }

    /**
     * Get formatted actual cost
     */
    public function getFormattedActualCostAttribute()
    {
        return $this->actual_cost ? 'KES ' . number_format($this->actual_cost, 2) : 'N/A';
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'appliance' => 'Appliance',
            'structural' => 'Structural',
            'hvac' => 'HVAC',
            'pest_control' => 'Pest Control',
            'cleaning' => 'Cleaning',
            'other' => 'Other',
        ];

        return $labels[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Mark as in progress
     */
    public function markAsInProgress($scheduledDate = null, $contractorName = null, $contractorPhone = null)
    {
        $this->update([
            'status' => 'in_progress',
            'scheduled_date' => $scheduledDate ?? $this->scheduled_date,
            'contractor_name' => $contractorName ?? $this->contractor_name,
            'contractor_phone' => $contractorPhone ?? $this->contractor_phone,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted($actualCost = null, $landlordNotes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'actual_cost' => $actualCost ?? $this->actual_cost,
            'landlord_notes' => $landlordNotes ?? $this->landlord_notes,
        ]);
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'landlord_notes' => $reason ?? $this->landlord_notes,
        ]);
    }
}

// Made with Bob
