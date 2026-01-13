<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'phone',
        'address',
        'vehicle_type',
        'plate_number',
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
