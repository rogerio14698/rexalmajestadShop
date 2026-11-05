<?php

namespace App\Services;

use App\Exceptions\CommerceConfigurationException;
use App\Models\TaxRate;

class TaxCalculator
{
    public function rateFor(string $countryCode, ?string $regionCode, string $taxClass): int
    {
        $query = TaxRate::query()->effective($countryCode, $regionCode, $taxClass);

        $rate = $regionCode === null
            ? $query->whereNull('region_code')->orderByDesc('valid_from')->first()
            : $query->where('region_code', strtoupper($regionCode))->orderByDesc('valid_from')->first()
                ?? TaxRate::query()->effective($countryCode, null, $taxClass)->whereNull('region_code')->orderByDesc('valid_from')->first();

        if (! $rate) {
            throw new CommerceConfigurationException(
                "Falta configurar el IVA para {$countryCode}/".($regionCode ?? 'sin región')."/{$taxClass}.",
            );
        }

        return $rate->rate_basis_points;
    }

    /**
     * Returns amounts in cents. In tax-inclusive mode, gross remains the entered price.
     *
     * @return array{net_cents: int, tax_cents: int, total_cents: int}
     */
    public function calculate(int $priceCents, int $rateBasisPoints, bool $pricesIncludeTax): array
    {
        if ($priceCents < 0 || $rateBasisPoints < 0) {
            throw new \InvalidArgumentException('El precio y el tipo impositivo deben ser positivos.');
        }

        if ($pricesIncludeTax) {
            $denominator = 10_000 + $rateBasisPoints;
            $netCents = intdiv(($priceCents * 10_000) + intdiv($denominator, 2), $denominator);
            $taxCents = $priceCents - $netCents;
            $totalCents = $priceCents;
        } else {
            $netCents = $priceCents;
            $taxCents = intdiv(($netCents * $rateBasisPoints) + 5_000, 10_000);
            $totalCents = $netCents + $taxCents;
        }

        return [
            'net_cents' => $netCents,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
        ];
    }
}
