<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'country_codes', 'region_codes', 'is_active'];

    protected function casts(): array
    {
        return [
            'country_codes' => 'array',
            'region_codes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function setCountryCodesAttribute(array $countryCodes): void
    {
        $this->attributes['country_codes'] = json_encode(array_values(array_unique(array_map(
            fn (string $code) => strtoupper($code),
            $countryCodes,
        ))), JSON_THROW_ON_ERROR);
    }

    public function setRegionCodesAttribute(?array $regionCodes): void
    {
        $this->attributes['region_codes'] = empty($regionCodes)
            ? null
            : json_encode(array_values(array_unique(array_map(
                fn (string $code) => strtoupper($code),
                $regionCodes,
            ))), JSON_THROW_ON_ERROR);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }
}
