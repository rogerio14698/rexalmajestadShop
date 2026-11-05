<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'artwork_id',
        'title',
        'slug',
        'quantity',
        'unit_price_cents',
        'net_cents',
        'tax_cents',
        'total_cents',
        'tax_rate_basis_points',
        'tax_class',
        'tax_country_code',
        'weight_grams',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'net_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'tax_rate_basis_points' => 'integer',
            'weight_grams' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
