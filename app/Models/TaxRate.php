<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TaxRate extends Model
{
    protected $fillable = [
        'country_code',
        'region_code',
        'tax_class',
        'rate_basis_points',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_basis_points' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function setCountryCodeAttribute(string $countryCode): void
    {
        $this->attributes['country_code'] = strtoupper($countryCode);
    }

    public function setRegionCodeAttribute(?string $regionCode): void
    {
        $this->attributes['region_code'] = $regionCode === null ? null : strtoupper($regionCode);
    }

    public function scopeEffective(Builder $query, string $countryCode, ?string $regionCode, string $taxClass): Builder
    {
        $today = now()->toDateString();

        return $query->where('country_code', strtoupper($countryCode))
            ->where('tax_class', $taxClass)
            ->where('is_active', true)
            ->whereDate('valid_from', '<=', $today)
            ->where(fn (Builder $query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->where(function (Builder $query) use ($regionCode) {
                $query->whereNull('region_code');

                if ($regionCode !== null) {
                    $query->orWhere('region_code', strtoupper($regionCode));
                }
            });
    }
}
