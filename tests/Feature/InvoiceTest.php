<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Services\CheckoutService;
use App\Services\InvoiceService;
use App\Services\OrderPaymentProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_issues_immutable_numbered_invoice_and_correction_offsets_vat_report(): void
    {
        config([
            'commerce.prices_include_tax' => true,
            'commerce.business.legal_name' => 'Emisor de prueba',
            'commerce.business.tax_id' => 'TEST-123',
            'commerce.business.address' => 'Dirección de prueba 1',
            'commerce.business.city' => 'Madrid',
            'commerce.business.postal_code' => '28001',
            'commerce.business.country_code' => 'ES',
            'commerce.business.email' => 'fiscal@example.test',
        ]);
        $artwork = Artwork::factory()->create(['price_cents' => 10000]);
        $cart = Cart::query()->create(['guest_token' => (string) Str::uuid()]);
        $cart->items()->create(['artwork_id' => $artwork->getKey(), 'quantity' => 1]);
        $this->configureSpanishRates();
        $order = app(CheckoutService::class)->createPendingOrder($cart, $this->customerDetails(), null);

        $this->assertTrue(app(OrderPaymentProcessor::class)->confirm($order, 'stripe', 'cs_test_invoice', 10500, 'EUR'));

        $invoice = Invoice::query()->where('order_id', $order->getKey())->firstOrFail();
        $this->assertSame('RMA-'.now()->format('Y').'-000001', $invoice->number);
        $this->assertSame(10500, $invoice->total_cents);
        $this->assertSame('Emisor de prueba', $invoice->issuer_snapshot['legal_name']);

        try {
            $invoice->update(['customer_name' => 'Cambio no permitido']);
            $this->fail('Issued invoices must not be mutable.');
        } catch (LogicException) {
            $this->assertSame('Persona compradora', $invoice->fresh()->customer_name);
        }

        $correction = app(InvoiceService::class)->createCorrection($invoice, 'Devolución completa');
        $this->assertSame('credit_note', $correction->type);
        $this->assertSame($invoice->getKey(), $correction->corrects_invoice_id);
        $this->assertSame('RMA-'.now()->format('Y').'-000002', $correction->number);
        $this->assertSame(0, app(InvoiceService::class)->vatReport(
            now()->startOfYear()->toDateString(),
            now()->endOfYear()->toDateString(),
        )->sum('tax_cents'));
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
