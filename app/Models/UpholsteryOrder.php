<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpholsteryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'unit_type',
        'unit_year_model',
        'unit_color',
        'services',
        'description',
        'photo_path',
        'installation_date',
        'downpayment',
        'balance',
    ];

    protected $casts = [
        'services' => 'json',
        'installation_date' => 'date',
        'downpayment' => 'integer',
        'balance' => 'integer',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Accessors
    public function getServicesListAttribute()
    {
        $serviceLabels = [
            'seat_cover' => 'Seat Cover',
            'ceiling' => 'Ceiling',
            'sidings' => 'Sidings',
            'rubber_mattings' => 'Rubber Mattings',
            'front_mattings' => 'Front Mattings',
        ];

        $selected = [];
        if (is_array($this->services)) {
            foreach ($this->services as $key => $value) {
                if ($value && isset($serviceLabels[$key])) {
                    $selected[] = $serviceLabels[$key];
                }
            }
        }
        return implode(', ', $selected);
    }
}
