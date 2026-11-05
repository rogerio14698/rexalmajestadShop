<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\OrderPaymentProcessor;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly OrderPaymentProcessor $paymentProcessor,
        private readonly StripeCheckoutService $stripeCheckoutService,
    ) {}

    public function __invoke(Request $request)
    {
        $secret = config('cashier.webhook.secret');

        abort_if(blank($secret), 503, 'Stripe webhook secret is not configured.');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret,
            );
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            Log::warning('Invalid Stripe webhook signature or payload.', ['exception' => $exception->getMessage()]);

            return response('Invalid webhook signature or payload.', 400);
        }

        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;
        $order = is_numeric($orderId) ? Order::query()->find($orderId) : null;

        if (! $order) {
            Log::error('Stripe reported a paid checkout without a matching order.', ['event_id' => $event->id]);

            return response('Order not found.', 404);
        }

        if (($session->payment_status ?? null) !== 'paid') {
            return response()->json(['received' => true]);
        }

        $amount = (int) ($session->amount_total ?? 0);
        $currency = strtoupper((string) ($session->currency ?? ''));
        $providerReference = (string) $session->id;
        $payload = $event->toArray();
        $accepted = false;

        DB::transaction(function () use ($event, $payload, $order, $amount, $currency, $providerReference, &$accepted) {
            $record = WebhookEvent::query()->firstOrCreate(
                ['provider' => 'stripe', 'event_id' => $event->id],
                ['payload' => $payload],
            );

            $record = WebhookEvent::query()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();

            if ($record->processed_at) {
                $accepted = true;

                return;
            }

            $accepted = $this->paymentProcessor->confirm(
                $order,
                'stripe',
                $providerReference,
                $amount,
                $currency,
                $payload,
            );

            if (! $accepted) {
                Log::error('Stripe payment could not be reconciled to its reserved order.', [
                    'event_id' => $event->id,
                    'order_id' => $order->getKey(),
                    'amount_cents' => $amount,
                    'currency' => $currency,
                ]);
            }

            if ($accepted) {
                $record->update(['processed_at' => now()]);
            }
        });

        if ($accepted) {
            return response()->json(['received' => true]);
        }

        $paymentIntentId = $session->payment_intent ?? null;

        if (! is_string($paymentIntentId) || $paymentIntentId === '') {
            Log::critical('Paid Stripe session could not be refunded because it has no payment intent.', [
                'event_id' => $event->id,
                'order_id' => $order->getKey(),
            ]);

            return response('Payment reconciliation requires manual review.', 409);
        }

        $this->stripeCheckoutService->refundUnexpectedPayment($paymentIntentId, $event->id);
        Payment::query()
            ->where('order_id', $order->getKey())
            ->where('provider', 'stripe')
            ->where('provider_reference', $providerReference)
            ->update(['status' => 'refunded']);
        WebhookEvent::query()
            ->where('provider', 'stripe')
            ->where('event_id', $event->id)
            ->update(['processed_at' => now()]);

        Log::warning('Unexpected Stripe payment was refunded after order reconciliation failed.', [
            'event_id' => $event->id,
            'order_id' => $order->getKey(),
        ]);

        return response()->json(['received' => true, 'refunded' => true]);
    }
}
