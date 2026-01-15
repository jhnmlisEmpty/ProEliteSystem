<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vip extends Model
{
    use HasFactory;

    protected $fillable = [
        'stepboard_pcs',
        'stepboard_amount',
        'engine_bay_pcs',
        'engine_bay_amount',
        'console_box_pcs',
        'console_box_amount',
        'description',
        'photo',
        'total_amount',
    ];

    protected $casts = [
        'stepboard_pcs' => 'integer',
        'stepboard_amount' => 'integer',
        'engine_bay_pcs' => 'integer',
        'engine_bay_amount' => 'integer',
        'console_box_pcs' => 'integer',
        'console_box_amount' => 'integer',
        'total_amount' => 'integer',
    ];

    // Relationships
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors & Mutators
    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedStepboardAmountAttribute(): string
    {
        return number_format($this->stepboard_amount, 2);
    }

    public function getFormattedEngineBayAmountAttribute(): string
    {
        return number_format($this->engine_bay_amount, 2);
    }

    public function getFormattedConsoleBoxAmountAttribute(): string
    {
        return number_format($this->console_box_amount, 2);
    }

    // Methods
    public function calculateTotalAmount(): int
    {
        return $this->stepboard_amount + $this->engine_bay_amount + $this->console_box_amount;
    }

    public function updateTotalAmount(): void
    {
        $this->total_amount = $this->calculateTotalAmount();
        $this->save();
    }
}
