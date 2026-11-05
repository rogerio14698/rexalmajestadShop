<?php

namespace App\Services;

use App\Models\Order;
use Laravel\Cashier\Cashier;
use RuntimeException;

class StripeCheckoutService
{
    public function createSession(Order $order): object
    {
        if (blank(config('cashier.secret'))) {
            throw new RuntimeException('Stripe no está configurado: falta STRIPE_SECRET.');
        }

        $lineItems = $order->items()
            ->where('slug', '<>', 'shipping')
            ->get()
            ->map(fn ($item) => [
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => $item->total_cents,
                    'product_data' => ['name' => $item->title],
                ],
            ])->all();

        if ($order->shipping_cents > 0) {
            $lineItems[] = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => $order->shipping_cents,
                    'product_data' => ['name' => 'Envío'],
                ],
            ];
        }

        return Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'client_reference_id' => $order->number,
            'metadata' => [
                'order_id' => (string) $order->getKey(),
                'order_number' => $order->number,
            ],
            'expires_at' => $order->reserve_expires_at->timestamp,
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.show'),
        ]);
    }

    public function refundUnexpectedPayment(string $paymentIntentId, string $eventId): void
    {
        if (blank(config('cashier.secret'))) {
            throw new RuntimeException('Stripe no está configurado: falta STRIPE_SECRET.');
        }

        Cashier::stripe()->refunds->create(
            ['payment_intent' => $paymentIntentId],
            ['idempotency_key' => 'refund-webhook-'.$eventId],
        );
    }
}
