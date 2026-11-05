<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaypalOrdersService
{
    /**
     * @return array<string, mixed>
     */
    public function createOrder(Order $order): array
    {
        return $this->api()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->number,
                'custom_id' => (string) $order->getKey(),
                'invoice_id' => $order->number,
                'amount' => [
                    'currency_code' => $order->currency,
                    'value' => number_format($order->total_cents / 100, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
                'return_url' => route('payments.paypal.return', ['number' => $order->number]),
                'cancel_url' => route('checkout.show'),
            ],
        ])->throw()->json();
    }

    /**
     * Capturing does not mark the local order paid; the verified webhook does.
     *
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function captureOrder(Order $order, string $providerOrderId): array
    {
        $response = $this->api()->withHeaders(['PayPal-Request-Id' => 'capture-'.$order->number])
            ->post("/v2/checkout/orders/{$providerOrderId}/capture")
            ->throw()
            ->json();

        $capture = data_get($response, 'purchase_units.0.payments.captures.0');
        $capturedCents = (int) round(((float) data_get($capture, 'amount.value', '0')) * 100);

        if (data_get($capture, 'status') !== 'COMPLETED'
            || data_get($capture, 'amount.currency_code') !== $order->currency
            || $capturedCents !== $order->total_cents) {
            throw new RuntimeException('PayPal devolvió un importe o estado de captura inesperado.');
        }

        $order->payment()->updateOrCreate(
            ['provider' => 'paypal'],
            [
                'provider_reference' => $providerOrderId,
                'status' => 'captured_pending_webhook',
                'amount_cents' => $capturedCents,
                'currency' => $order->currency,
                'metadata' => $response,
                'captured_at' => now(),
            ],
        );

        return $response;
    }

    public function refundCapture(string $captureId, string $eventId): array
    {
        $response = $this->api()
            ->withHeaders(['PayPal-Request-Id' => substr(hash('sha256', 'refund-'.$eventId), 0, 32)])
            ->post("/v2/payments/captures/{$captureId}/refund")
            ->throw()
            ->json();

        if (! is_array($response)) {
            throw new RuntimeException('PayPal no devolvió una respuesta válida al reembolso.');
        }

        return $response;
    }

    public function verifyWebhook(Request $request): bool
    {
        $webhookId = config('services.paypal.webhook_id');

        if (blank($webhookId)) {
            throw new RuntimeException('PayPal no está configurado: falta PAYPAL_WEBHOOK_ID.');
        }

        $headers = [
            'auth_algo' => $request->header('paypal-auth-algo'),
            'cert_url' => $request->header('paypal-cert-url'),
            'transmission_id' => $request->header('paypal-transmission-id'),
            'transmission_sig' => $request->header('paypal-transmission-sig'),
            'transmission_time' => $request->header('paypal-transmission-time'),
        ];

        if (in_array(null, $headers, true)) {
            return false;
        }

        $response = $this->api()->post('/v1/notifications/verify-webhook-signature', [
            ...$headers,
            'webhook_id' => $webhookId,
            'webhook_event' => $request->all(),
        ])->throw()->json();

        return data_get($response, 'verification_status') === 'SUCCESS';
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->withToken($this->accessToken())
            ->timeout(15);
    }

    private function accessToken(): string
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        if (blank($clientId) || blank($secret)) {
            throw new RuntimeException('PayPal no está configurado: faltan PAYPAL_CLIENT_ID o PAYPAL_SECRET.');
        }

        $cacheKey = 'paypal.access_token.'.hash('sha256', $clientId);
        $token = Cache::get($cacheKey);

        if ($token) {
            return $token;
        }

        $response = Http::baseUrl($this->baseUrl())
            ->withBasicAuth($clientId, $secret)
            ->asForm()
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json();

        $token = data_get($response, 'access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal no devolvió un token de acceso válido.');
        }

        Cache::put($cacheKey, $token, now()->addSeconds(max(60, (int) data_get($response, 'expires_in', 300) - 60)));

        return $token;
    }

    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
