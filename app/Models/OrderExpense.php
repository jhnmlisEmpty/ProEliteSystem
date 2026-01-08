<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExpense extends Model
{
    use HasFactory;

    protected $table = 'order_expenses';

    protected $fillable = [
        'order_id',
        'description',
        'my_cost',
        'charge_client',
        'is_billable',
    ];

    protected $casts = [
        'my_cost' => 'integer',
        'charge_client' => 'integer',
        'is_billable' => 'boolean',
    ];

    /**
     * Get the order this expense belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
