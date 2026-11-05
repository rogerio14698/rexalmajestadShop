<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingInquiry extends Model
{
    protected $fillable = ['artwork_id', 'name', 'email', 'country_code', 'postal_code', 'message'];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
