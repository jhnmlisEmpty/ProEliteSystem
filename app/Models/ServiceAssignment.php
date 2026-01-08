<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceAssignment extends Model
{
    use HasFactory;

    protected $table = 'service_assignments';

    protected $fillable = [
        'order_id',
        'service_id',
        'employee_id',
    ];

    /**
     * Get the order this assignment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the employee assigned
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
