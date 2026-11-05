<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Artwork;
use App\Models\Cart;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Services\CheckoutService;
use App\Services\OrderPaymentProcessor;
use App\Services\ShippingCalculator;
use App\Services\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_reserves_stock_and_confirmed_payment_consumes_it_once(): void
    {
        config(['commerce.prices_include_tax' => true]);
        $artwork = Artwork::factory()->create(['price_cents' => 10000, 'stock' => 1]);
        $cart = Cart::query()->create(['guest_token' => (string) Str::uuid()]);
        $cart->items()->create(['artwork_id' => $artwork->getKey(), 'quantity' => 1]);
        $this->configureSpanishRates();

        $order = app(CheckoutService::class)->createPendingOrder($cart, $this->customerDetails(), null);

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(10500, $order->total_cents);
        $this->assertSame(1823, $order->tax_cents);
        $this->assertDatabaseHas('stock_reservations', [
            'order_id' => $order->getKey(),
            'artwork_id' => $artwork->getKey(),
            'quantity' => 1,
        ]);
        $this->assertSame(0, app(\App\Services\CartService::class)->availableQuantity($artwork));

        $processor = app(OrderPaymentProcessor::class);
        $this->assertTrue($processor->confirm($order, 'stripe', 'cs_test_checkout', 10500, 'EUR'));
        $this->assertTrue($processor->confirm($order->fresh(), 'stripe', 'cs_test_checkout', 10500, 'EUR'));

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(0, $artwork->fresh()->stock);
        $this->assertTrue($artwork->fresh()->is_sold);
        $this->assertSame(1, $order->reservations()->whereNotNull('consumed_at')->count());
    }

    public function test_payment_with_wrong_amount_or_expired_reservation_is_not_confirmed(): void
    {
        config(['commerce.prices_include_tax' => true]);
        $artwork = Artwork::factory()->create(['price_cents' => 10000]);
        $cart = Cart::query()->create(['guest_token' => (string) Str::uuid()]);
        $cart->items()->create(['artwork_id' => $artwork->getKey(), 'quantity' => 1]);
        $this->configureSpanishRates();
        $order = app(CheckoutService::class)->createPendingOrder($cart, $this->customerDetails(), null);
        $processor = app(OrderPaymentProcessor::class);

        $this->assertFalse($processor->confirm($order, 'stripe', 'cs_test_bad', 10499, 'EUR'));
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);

        $order->update(['reserve_expires_at' => now()->subMinute()]);

        $this->assertFalse($processor->confirm($order, 'stripe', 'cs_test_expired', 10500, 'EUR'));
        $this->assertSame(1, $artwork->fresh()->stock);
    }

    public function test_shipping_calculator_prefers_matching_region_over_general_zone(): void
    {
        config(['commerce.currency' => 'EUR']);
        $general = ShippingZone::query()->create([
            'name' => 'España general',
            'country_codes' => ['ES'],
            'region_codes' => null,
            'is_active' => true,
        ]);
        $specific = ShippingZone::query()->create([
            'name' => 'Madrid',
            'country_codes' => ['ES'],
            'region_codes' => ['MD'],
            'is_active' => true,
        ]);
        $general->rates()->create(['min_weight_grams' => 0, 'max_weight_grams' => 10000, 'price_cents' => 1000]);
        $specific->rates()->create(['min_weight_grams' => 0, 'max_weight_grams' => 10000, 'price_cents' => 500]);

        $quote = app(ShippingCalculator::class)->quote('ES', 'MD', 500);

        $this->assertSame($specific->getKey(), $quote['zone']->getKey());
        $this->assertSame(500, $quote['rate']->price_cents);
    }

    public function test_shipping_calculator_uses_only_rates_in_the_shop_currency(): void
    {
        config(['commerce.currency' => 'USD']);
        $zone = ShippingZone::query()->create([
            'name' => 'España',
            'country_codes' => ['ES'],
            'region_codes' => null,
            'is_active' => true,
        ]);
        $zone->rates()->create([
            'min_weight_grams' => 0,
            'max_weight_grams' => 10000,
            'price_cents' => 500,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $usdRate = $zone->rates()->create([
            'min_weight_grams' => 0,
            'max_weight_grams' => 10000,
            'price_cents' => 700,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $quote = app(ShippingCalculator::class)->quote('ES', '', 500);

        $this->assertSame($usdRate->getKey(), $quote['rate']->getKey());
    }

    public function test_guest_cart_can_be_added_and_is_not_accessible_from_another_session(): void
    {
        $artwork = Artwork::factory()->create(['stock' => 2]);

        $this->post(route('cart.add', ['artwork' => $artwork]), ['quantity' => 1])
            ->assertRedirect(route('cart.show'));
        $item = \App\Models\CartItem::query()->firstOrFail();

        $this->get(route('cart.show'))->assertOk()->assertSee($artwork->title);
        $this->flushSession();
        $this->patch(route('cart.update', ['item' => $item]), ['quantity' => 1])->assertNotFound();
    }

    private function configureSpanishRates(): void
    {
        foreach (['original_art', 'shipping'] as $taxClass) {
            TaxRate::query()->create([
                'country_code' => 'ES',
                'region_code' => null,
                'tax_class' => $taxClass,
                'rate_basis_points' => 2100,
                'valid_from' => now()->subYear()->toDateString(),
                'is_active' => true,
            ]);
        }

        $zone = ShippingZone::query()->create([
            'name' => 'España',
            'country_codes' => ['ES'],
            'region_codes' => null,
            'is_active' => true,
        ]);
        $zone->rates()->create([
            'min_weight_grams' => 0,
            'max_weight_grams' => 10000,
            'price_cents' => 500,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function customerDetails(): array
    {
        return [
            'email' => 'buyer@example.test',
            'shipping_recipient' => 'Persona compradora',
            'shipping_line_1' => 'Calle de prueba 1',
            'shipping_city' => 'Madrid',
            'shipping_region' => 'MD',
            'shipping_postal_code' => '28001',
            'shipping_country' => 'ES',
            'billing_recipient' => 'Persona compradora',
            'billing_line_1' => 'Calle de prueba 1',
            'billing_city' => 'Madrid',
            'billing_region' => 'MD',
            'billing_postal_code' => '28001',
            'billing_country' => 'ES',
        ];
    }
}
