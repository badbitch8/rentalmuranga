<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'booking_id',
        'user_id',
        'amount',
        'payment_type',
        'payment_method',
        'transaction_id',
        'mpesa_receipt_number',
        'phone_number',
        'status',
        'payment_date',
        'description',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the booking for the payment
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who made the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by payment type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope a query to filter by payment method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to filter by M-Pesa payments
     */
    public function scopeMpesa($query)
    {
        return $query->where('payment_method', 'mpesa');
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if payment is via M-Pesa
     */
    public function isMpesa(): bool
    {
        return $this->payment_method === 'mpesa';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    /**
     * Get payment type label
     */
    public function getPaymentTypeLabelAttribute()
    {
        $labels = [
            'deposit' => 'Security Deposit',
            'rent' => 'Monthly Rent',
            'maintenance' => 'Maintenance Fee',
            'penalty' => 'Penalty Fee',
            'refund' => 'Refund',
        ];

        return $labels[$this->payment_type] ?? ucfirst($this->payment_type);
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'mpesa' => 'M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'card' => 'Card Payment',
        ];

        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'blue',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($transactionId = null, $receiptNumber = null)
    {
        $this->update([
            'status' => 'completed',
            'payment_date' => now(),
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'mpesa_receipt_number' => $receiptNumber ?? $this->mpesa_receipt_number,
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null)
    {
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['failure_reason'] = $reason;
        }

        $this->update([
            'status' => 'failed',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded($refundTransactionId = null)
    {
        $metadata = $this->metadata ?? [];
        if ($refundTransactionId) {
            $metadata['refund_transaction_id'] = $refundTransactionId;
            $metadata['refunded_at'] = now()->toDateTimeString();
        }

        $this->update([
            'status' => 'refunded',
            'metadata' => $metadata,
        ]);
    }
}

// Made with Bob
