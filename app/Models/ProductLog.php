<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    protected $fillable = [
        'product_id',
        'change_amount',
        'reason',
        'reference_id',
    ];

    protected $casts = [
        'change_amount' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
