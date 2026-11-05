<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\OrderPaymentProcessor;
use App\Services\PaypalOrdersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaypalWebhookController extends Controller
{
    public function __construct(
        private readonly PaypalOrdersService $paypalOrdersService,
        private readonly OrderPaymentProcessor $paymentProcessor,
    ) {}

    public function __invoke(Request $request)
    {
        try {
            if (! $this->paypalOrdersService->verifyWebhook($request)) {
                Log::warning('PayPal webhook signature verification failed.');

                return response('Invalid PayPal webhook signature.', 400);
            }
        } catch (RuntimeException $exception) {
            report($exception);

            return response('PayPal webhook verification is not configured.', 503);
        }

        $eventId = $request->input('id');
        $eventType = $request->input('event_type');

        if (! is_string($eventId) || ! is_string($eventType)) {
            return response('Invalid PayPal webhook payload.', 400);
        }

        if ($eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
            return response()->json(['received' => true]);
        }

        $resource = $request->input('resource', []);
        $providerOrderId = data_get($resource, 'supplementary_data.related_ids.order_id');
        $payment = Payment::query()
            ->where('provider', 'paypal')
            ->where('provider_reference', $providerOrderId)
            ->with('order')
            ->first();

        if (! $payment) {
            Log::error('PayPal reported a capture without a matching order.', ['event_id' => $eventId]);

            return response('Order not found.', 404);
        }

        $captureAmount = (int) round(((float) data_get($resource, 'amount.value', '0')) * 100);
        $currency = strtoupper((string) data_get($resource, 'amount.currency_code', ''));
        $payload = $request->all();
        $accepted = false;

        if (data_get($resource, 'status') !== 'COMPLETED') {
            return response()->json(['received' => true]);
        }

        DB::transaction(function () use ($eventId, $payload, $payment, $captureAmount, $currency, $providerOrderId, &$accepted) {
            $record = WebhookEvent::query()->firstOrCreate(
                ['provider' => 'paypal', 'event_id' => $eventId],
                ['payload' => $payload],
            );
            $record = WebhookEvent::query()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();

            if ($record->processed_at) {
                $accepted = true;

                return;
            }

            $accepted = $this->paymentProcessor->confirm(
                $payment->order,
                'paypal',
                (string) $providerOrderId,
                $captureAmount,
                $currency,
                $payload,
            );

            if (! $accepted) {
                Log::error('PayPal capture could not be reconciled to its order.', [
                    'event_id' => $eventId,
                    'order_id' => $payment->order_id,
                    'amount_cents' => $captureAmount,
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

            $captureId = data_get($resource, 'id');

            if (! is_string($captureId) || $captureId === '') {
                Log::critical('Completed PayPal capture could not be refunded because it has no capture ID.', [
                    'event_id' => $eventId,
                    'order_id' => $payment->order_id,
                ]);

                return response('Payment reconciliation requires manual review.', 409);
            }

            $this->paypalOrdersService->refundCapture($captureId, $eventId);
            $payment->update(['status' => 'refunded']);
            WebhookEvent::query()
                ->where('provider', 'paypal')
                ->where('event_id', $eventId)
                ->update(['processed_at' => now()]);

            Log::warning('Unexpected PayPal capture was refunded after order reconciliation failed.', [
                'event_id' => $eventId,
                'order_id' => $payment->order_id,
            ]);

            return response()->json(['received' => true, 'refunded' => true]);
    }
}
