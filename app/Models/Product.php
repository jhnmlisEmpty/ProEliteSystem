<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'image',
        'type',
        'stock_qty',
        'buy_price',
        'sell_price',
        'alert_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_qty' => 'integer',
        'buy_price' => 'integer',
        'sell_price' => 'integer',
        'alert_limit' => 'integer',
    ];

    /**
     * Get the inventory logs for the product.
     */
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function productLogs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product stock is below alert limit.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->alert_limit;
    }

    /**
     * Get formatted stock quantity.
     *
     * @return string
     */
    public function getFormattedStockAttribute(): string
    {
        return number_format($this->stock_qty, 2);
    }

    /**
     * Get formatted buy price.
     *
     * @return string
     */
    public function getFormattedBuyPriceAttribute(): string
    {
        return '$' . number_format($this->buy_price, 2);
    }

    /**
     * Get formatted sell price.
     *
     * @return string
     */
    public function getFormattedSellPriceAttribute(): string
    {
        return $this->sell_price ? '$' . number_format($this->sell_price, 2) : 'N/A';
    }

    /**
     * Scope a query to only include retail products.
     */
    public function scopeRetail($query)
    {
        return $query->where('type', 'retail');
    }

    /**
     * Scope a query to only include material products.
     */
    public function scopeMaterial($query)
    {
        return $query->where('type', 'material');
    }

    /**
     * Scope a query to only include low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_qty <= alert_limit');
    }
}
