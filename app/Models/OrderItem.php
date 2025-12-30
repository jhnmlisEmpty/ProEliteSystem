<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Accessors
    public function getItemNameAttribute()
    {
        if ($this->product_id) {
            return $this->product->name ?? 'Unknown Product';
        }
        if ($this->service_id) {
            return $this->service->name ?? 'Unknown Service';
        }
        return 'Unknown Item';
    }

    public function getItemTypeAttribute()
    {
        if ($this->product_id) {
            return 'product';
        }
        if ($this->service_id) {
            return 'service';
        }
        return 'unknown';
    }
}
