<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_webhook_rejects_an_invalid_signature(): void
    {
        config(['cashier.webhook.secret' => 'whsec_test']);

        $this->call('POST', route('payments.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
        ], '{"id":"evt_invalid"}')->assertStatus(400);
    }

    public function test_late_paid_stripe_checkout_is_idempotently_refunded(): void
    {
        $secret = 'whsec_test';
        config(['cashier.webhook.secret' => $secret]);
        $order = Order::query()->create([
            'number' => 'RMA-TEST-0001',
            'email' => 'buyer@example.test',
            'status' => OrderStatus::Cancelled,
            'currency' => 'EUR',
            'subtotal_cents' => 1000,
            'shipping_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 1000,
            'shipping_address_snapshot' => ['recipient' => 'Cliente'],
            'billing_address_snapshot' => ['recipient' => 'Cliente'],
        ]);
        Payment::query()->create([
            'order_id' => $order->getKey(),
            'provider' => 'stripe',
            'provider_reference' => 'cs_test_late',
            'status' => 'pending',
            'amount_cents' => 1000,
            'currency' => 'EUR',
        ]);

        $payload = json_encode([
            'id' => 'evt_late_payment',
            'object' => 'event',
            'api_version' => '2025-03-31.basil',
            'created' => time(),
            'data' => [
                'object' => [
                    'id' => 'cs_test_late',
                    'object' => 'checkout.session',
                    'amount_total' => 1000,
                    'currency' => 'eur',
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_test_late',
                    'metadata' => ['order_id' => (string) $order->getKey()],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 1,
            'type' => 'checkout.session.completed',
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        $this->mock(StripeCheckoutService::class)
            ->shouldReceive('refundUnexpectedPayment')
            ->once()
            ->with('pi_test_late', 'evt_late_payment');

        $this->call('POST', route('payments.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload)->assertOk()->assertJson(['refunded' => true]);

        $this->assertSame('refunded', $order->payment()->firstOrFail()->status);
        $this->assertNotNull(WebhookEvent::query()
            ->where('provider', 'stripe')
            ->where('event_id', 'evt_late_payment')
            ->firstOrFail()->processed_at);
    }

    public function test_paypal_webhook_requires_verification_configuration(): void
    {
        config(['services.paypal.webhook_id' => null]);

        $this->postJson(route('payments.paypal.webhook'), ['id' => 'evt_test'])
            ->assertStatus(503);
    }
}
