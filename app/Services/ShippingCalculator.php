<?php

namespace App\Services;

use App\Exceptions\CommerceConfigurationException;
use App\Models\ShippingRate;
use App\Models\ShippingZone;

class ShippingCalculator
{
    /**
     * @return array{zone: ShippingZone, rate: ShippingRate}
     */
    public function quote(string $countryCode, string $regionCode, int $weightGrams): array
    {
        $countryCode = strtoupper($countryCode);
        $regionCode = strtoupper($regionCode);
        $zones = ShippingZone::query()->where('is_active', true)->with('rates')->get()
            ->sortByDesc(fn (ShippingZone $candidate) => $candidate->region_codes !== null);

        $zone = $zones->first(fn (ShippingZone $candidate) => in_array($countryCode, $candidate->country_codes, true)
            && (empty($candidate->region_codes) || in_array($regionCode, $candidate->region_codes, true)));

        if (! $zone) {
            throw new CommerceConfigurationException("No hay una zona de envío configurada para {$countryCode}/{$regionCode}.");
        }

        $rate = $zone->rates->first(fn (ShippingRate $candidate) => $candidate->is_active
            && strtoupper($candidate->currency) === strtoupper(config('commerce.currency', 'EUR'))
            && $weightGrams >= $candidate->min_weight_grams
            && $weightGrams <= $candidate->max_weight_grams);

        if (! $rate) {
            throw new CommerceConfigurationException("No hay una tarifa de envío configurada para {$weightGrams} gramos.");
        }

        return ['zone' => $zone, 'rate' => $rate];
    }
}
