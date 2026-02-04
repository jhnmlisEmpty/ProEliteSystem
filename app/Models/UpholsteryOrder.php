<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'seat_cover_amount',
        'seat_cover_description',
        'ceiling_amount',
        'ceiling_description',
        'sidings_amount',
        'sidings_description',
        'rubber_mattings_amount',
        'rubber_mattings_description',
        'front_mattings_amount',
        'front_mattings_description',
        'headrest_amount',
        'headrest_description',
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

    public function assignments(): HasMany
    {
        return $this->hasMany(UpholsteryAssignment::class);
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
