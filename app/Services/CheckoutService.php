<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\CommerceConfigurationException;
use App\Models\Artwork;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingInquiry;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly TaxCalculator $taxCalculator,
        private readonly ShippingCalculator $shippingCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public function createPendingOrder(Cart $cart, array $customer, ?int $userId): Order
    {
        return DB::transaction(function () use ($cart, $customer, $userId) {
            $cartItems = $cart->items()->orderBy('artwork_id')->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'El carrito está vacío.']);
            }

            $country = strtoupper($customer['shipping_country']);
            $region = strtoupper($customer['shipping_region']);
            $priceIncludesTax = (bool) config('commerce.prices_include_tax');
            $preparedItems = [];
            $weight = 0;
            $subtotal = 0;
            $itemTax = 0;

            foreach ($cartItems as $cartItem) {
                $artwork = Artwork::query()->lockForUpdate()->findOrFail($cartItem->artwork_id);

                if (! $artwork->canPurchase() || $artwork->ask_shipping) {
                    throw ValidationException::withMessages([
                        'cart' => "La obra «{$artwork->title}» no puede incluirse en el checkout.",
                    ]);
                }

                $reserved = StockReservation::query()
                    ->where('artwork_id', $artwork->getKey())
                    ->whereNull('released_at')
                    ->whereNull('consumed_at')
                    ->where('expires_at', '>', now())
                    ->sum('quantity');

                if ($artwork->stock - $reserved < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "No hay suficiente stock para «{$artwork->title}».",
                    ]);
                }

                $rate = $this->taxCalculator->rateFor($country, $region, $artwork->tax_class);
                $line = $this->taxCalculator->calculate(
                    $artwork->price_cents * $cartItem->quantity,
                    $rate,
                    $priceIncludesTax,
                );
                $preparedItems[] = compact('artwork', 'cartItem', 'rate', 'line');
                $subtotal += $line['total_cents'];
                $itemTax += $line['tax_cents'];
                $weight += $artwork->weight_grams * $cartItem->quantity;
            }

            $shippingQuote = $this->shippingCalculator->quote($country, $region, $weight);
            $shippingTaxRate = $this->taxCalculator->rateFor($country, $region, 'shipping');
            $shippingAmounts = $this->taxCalculator->calculate(
                $shippingQuote['rate']->price_cents,
                $shippingTaxRate,
                $priceIncludesTax,
            );
            $reservationExpiry = now()->addMinutes(30);
            $order = Order::query()->create([
                'user_id' => $userId,
                'shipping_zone_id' => $shippingQuote['zone']->getKey(),
                'email' => $customer['email'],
                'status' => OrderStatus::Pending,
                'currency' => $shippingQuote['rate']->currency,
                'subtotal_cents' => $subtotal,
                'shipping_cents' => $shippingAmounts['total_cents'],
                'tax_cents' => $itemTax + $shippingAmounts['tax_cents'],
                'total_cents' => $subtotal + $shippingAmounts['total_cents'],
                'shipping_address_snapshot' => $this->addressSnapshot($customer, 'shipping'),
                'billing_address_snapshot' => $this->addressSnapshot($customer, 'billing'),
                'reserve_expires_at' => $reservationExpiry,
            ]);
            $order->update(['number' => 'RMA-'.now()->format('Y').'-'.str_pad((string) $order->getKey(), 8, '0', STR_PAD_LEFT)]);

            foreach ($preparedItems as $prepared) {
                $order->items()->create([
                    'artwork_id' => $prepared['artwork']->getKey(),
                    'title' => $prepared['artwork']->title,
                    'slug' => $prepared['artwork']->slug,
                    'quantity' => $prepared['cartItem']->quantity,
                    'unit_price_cents' => $prepared['artwork']->price_cents,
                    'net_cents' => $prepared['line']['net_cents'],
                    'tax_cents' => $prepared['line']['tax_cents'],
                    'total_cents' => $prepared['line']['total_cents'],
                    'tax_rate_basis_points' => $prepared['rate'],
                    'tax_class' => $prepared['artwork']->tax_class,
                    'tax_country_code' => $country,
                    'weight_grams' => $prepared['artwork']->weight_grams,
                ]);
                $order->reservations()->create([
                    'artwork_id' => $prepared['artwork']->getKey(),
                    'quantity' => $prepared['cartItem']->quantity,
                    'expires_at' => $reservationExpiry,
                ]);
            }

            $order->items()->create([
                'title' => 'Envío',
                'slug' => 'shipping',
                'quantity' => 1,
                'unit_price_cents' => $shippingAmounts['total_cents'],
                'net_cents' => $shippingAmounts['net_cents'],
                'tax_cents' => $shippingAmounts['tax_cents'],
                'total_cents' => $shippingAmounts['total_cents'],
                'tax_rate_basis_points' => $shippingTaxRate,
                'tax_class' => 'shipping',
                'tax_country_code' => $country,
                'weight_grams' => $weight,
            ]);

            return $order->load('items');
        });
    }

    public function cancelAndRelease(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->status !== OrderStatus::Pending) {
                return;
            }

            $lockedOrder->reservations()->whereNull('released_at')->update(['released_at' => now()]);
            $lockedOrder->update(['status' => OrderStatus::Cancelled]);
        });
    }

    public function recordShippingInquiry(Artwork $artwork, array $details): ShippingInquiry
    {
        return ShippingInquiry::query()->create([
            'artwork_id' => $artwork->getKey(),
            'name' => $details['name'],
            'email' => $details['email'],
            'country_code' => strtoupper($details['country_code']),
            'postal_code' => $details['postal_code'] ?? null,
            'message' => $details['message'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $customer
     * @return array<string, string|null>
     */
    private function addressSnapshot(array $customer, string $prefix): array
    {
        return [
            'recipient' => $customer[$prefix.'_recipient'],
            'line_1' => $customer[$prefix.'_line_1'],
            'line_2' => $customer[$prefix.'_line_2'] ?? null,
            'city' => $customer[$prefix.'_city'],
            'region_code' => strtoupper($customer[$prefix.'_region']),
            'postal_code' => $customer[$prefix.'_postal_code'],
            'country_code' => strtoupper($customer[$prefix.'_country']),
            'tax_id' => $customer[$prefix.'_tax_id'] ?? null,
        ];
    }
}
