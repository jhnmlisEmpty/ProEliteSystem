<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalSpentAttribute(): int
    {
        return (int) ($this->orders()->sum('total_amount') ?? 0);
    }

    public function getTotalOrdersAttribute(): int
    {
        return (int) ($this->orders()->count() ?? 0);
    }
}
