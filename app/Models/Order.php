<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'vehicle_type',
        'plate_number',
        'type',
        'status',
        'payment_status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'integer',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Query Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Payment Status Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeFullyPaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Accessors
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }
}
