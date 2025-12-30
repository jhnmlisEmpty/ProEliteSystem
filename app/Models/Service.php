<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'base_labor_cost',
    ];

    protected $casts = [
        'base_labor_cost' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
