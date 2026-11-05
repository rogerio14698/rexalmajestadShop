<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaypalOrdersService;
use App\Services\StripeCheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly StripeCheckoutService $stripeCheckoutService,
        private readonly PaypalOrdersService $paypalOrdersService,
    ) {}

    public function show(Request $request): View
    {
        $cart = $this->cartService->forRequest($request)->load('items');

        abort_if($cart->items->isEmpty(), 404);

        return view('public.shop.checkout', compact('cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'payment_provider' => ['required', 'in:stripe,paypal'],
            'shipping_recipient' => ['required', 'string', 'max:255'],
            'shipping_line_1' => ['required', 'string', 'max:255'],
            'shipping_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_region' => ['required', 'string', 'max:32'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
            'shipping_tax_id' => ['nullable', 'string', 'max:40'],
            'billing_same' => ['nullable', 'boolean'],
            'billing_recipient' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:255'],
            'billing_line_1' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:255'],
            'billing_line_2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:120'],
            'billing_region' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:32'],
            'billing_postal_code' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:20'],
            'billing_country' => ['required_unless:billing_same,1', 'nullable', 'string', 'size:2'],
            'billing_tax_id' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'size:0'],
        ]);

        if ($request->boolean('billing_same')) {
            foreach (['recipient', 'line_1', 'line_2', 'city', 'region', 'postal_code', 'country', 'tax_id'] as $field) {
                $data['billing_'.$field] = $data['shipping_'.$field] ?? null;
            }
        }

        try {
            $order = $this->checkoutService->createPendingOrder(
                $this->cartService->forRequest($request),
                $data,
                $request->user()?->getKey(),
            );

            if ($data['payment_provider'] === 'stripe') {
                $session = $this->stripeCheckoutService->createSession($order);
                $paymentUrl = $session->url;
                $providerReference = $session->id;
            } else {
                $paypalOrder = $this->paypalOrdersService->createOrder($order);
                $paymentUrl = collect($paypalOrder['links'] ?? [])
                    ->firstWhere('rel', 'approve')['href'] ?? null;
                $providerReference = $paypalOrder['id'] ?? null;
            }

            if (! is_string($paymentUrl) || ! is_string($providerReference)) {
                throw new RuntimeException('El proveedor no devolvió un enlace de pago válido.');
            }

            $parts = parse_url($paymentUrl);
            $allowedHosts = $data['payment_provider'] === 'stripe'
                ? ['checkout.stripe.com']
                : ['www.paypal.com', 'www.sandbox.paypal.com'];

            if (($parts['scheme'] ?? null) !== 'https'
                || ! in_array(strtolower($parts['host'] ?? ''), $allowedHosts, true)) {
                throw new RuntimeException('El proveedor devolvió un dominio de pago no autorizado.');
            }

            $order->payment()->create([
                'provider' => $data['payment_provider'],
                'provider_reference' => $providerReference,
                'status' => 'pending',
                'amount_cents' => $order->total_cents,
                'currency' => $order->currency,
            ]);

            $this->cartService->forRequest($request)->items()->delete();

            return Redirect::away($paymentUrl);
        } catch (ApiErrorException|RequestException|RuntimeException $exception) {
            if (isset($order)) {
                $this->checkoutService->cancelAndRelease($order);
            }

            throw $exception;
        }
    }

    public function success(): View
    {
        return view('public.shop.success');
    }

    public function capturePaypal(Request $request, string $number): RedirectResponse
    {
        $order = Order::query()->where('number', $number)->with('payment')->firstOrFail();
        $providerOrderId = $request->query('token');

        abort_unless(
            $order->status === OrderStatus::Pending
            && $order->reserve_expires_at?->isFuture()
            && $order->payment?->provider === 'paypal'
            && is_string($providerOrderId)
            && hash_equals((string) $order->payment->provider_reference, $providerOrderId),
            403,
        );

        try {
            $this->paypalOrdersService->captureOrder($order, $providerOrderId);
        } catch (RequestException|RuntimeException $exception) {
            report($exception);
            throw ValidationException::withMessages(['payment' => 'No se pudo confirmar la captura con PayPal.']);
        }

        return redirect()->route('checkout.success');
    }
}
