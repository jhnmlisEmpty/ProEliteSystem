<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpholsteryAssignment extends Model
{
    use HasFactory;

    protected $table = 'upholstery_assignments';

    protected $fillable = [
        'order_id',
        'upholstery_id',
        'employee_id',
        'payment_status',
    ];

    /**
     * Get the order this assignment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the upholstery
     */
    public function upholstery(): BelongsTo
    {
        return $this->belongsTo(UpholsteryOrder::class);
    }

    /**
     * Get the employee assigned
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
