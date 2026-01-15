<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'service_id',
        'upholstery_id',
        'vip_id',
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

    public function upholstery(): BelongsTo
    {
        return $this->belongsTo(UpholsteryOrder::class, 'upholstery_id');
    }

    public function vip(): BelongsTo
    {
        return $this->belongsTo(Vip::class);
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
        if ($this->upholstery_id) {
            return $this->upholstery->title ?? 'Unknown Upholstery';
        }
        if ($this->vip_id) {
            return 'VIP Package';
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
        if ($this->upholstery_id) {
            return 'upholstery';
        }
        if ($this->vip_id) {
            return 'vip';
        }
        return 'unknown';
    }
}
