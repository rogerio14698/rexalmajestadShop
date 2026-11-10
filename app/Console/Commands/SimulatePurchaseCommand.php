<?php

namespace App\Console\Commands;

use App\Models\Artwork;
use App\Models\Cart;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\OrderPaymentProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulatePurchaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:simulate-purchase 
                            {--artwork= : ID de la obra a comprar (por defecto toma la primera disponible)}
                            {--user=admin@rexalmajestad.test : Correo del usuario que compra}
                            {--provider=stripe : Proveedor de pago a simular (stripe o paypal)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula un flujo completo de compra en Sandbox: carrito, reserva de stock, checkout y confirmación de pago';

    /**
     * Execute the console command.
     */
    public function handle(CheckoutService $checkoutService, OrderPaymentProcessor $paymentProcessor): int
    {
        $userEmail = (string) $this->option('user');
        $user = User::query()->where('email', $userEmail)->first();

        if (! $user) {
            $this->error("Usuario no encontrado con el email: {$userEmail}");

            return self::FAILURE;
        }

        $artworkId = $this->option('artwork');
        if ($artworkId) {
            $artwork = Artwork::query()->find($artworkId);
        } else {
            $artwork = Artwork::query()
                ->where('is_for_sale', true)
                ->where('is_sold', false)
                ->where('ask_shipping', false)
                ->where('stock', '>', 0)
                ->first();
        }

        if (! $artwork) {
            $this->error('No se ha encontrado ninguna obra disponible para compra directa en la tienda.');

            return self::FAILURE;
        }

        $this->info("Simulando compra para: {$user->name} ({$user->email})");
        $this->line(" Obra seleccionada: #{$artwork->id} «{$artwork->title}»");
        $this->line(" Precio base: " . number_format($artwork->price_cents / 100, 2, ',', '.') . " €");
        $this->line(" Stock antes de la compra: {$artwork->stock}");

        // 1. Obtener o crear carrito para la sesión de prueba
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['guest_token' => (string) Str::uuid()]
        );

        $cart->items()->delete();

        $cart->items()->create([
            'artwork_id' => $artwork->id,
            'quantity' => 1,
        ]);

        $this->info('✓ Carrito creado y obra agregada.');

        // Asegurar tipos de IVA y zona de envío básica para España si no existen
        $this->ensureTaxAndShippingRates();

        // 2. Datos del comprador
        $customerDetails = [
            'email' => $user->email,
            'shipping_recipient' => $user->name,
            'shipping_line_1' => 'Calle de Prueba San Jerónimo 12',
            'shipping_line_2' => 'Piso 3A',
            'shipping_city' => 'Madrid',
            'shipping_region' => 'MD',
            'shipping_postal_code' => '28014',
            'shipping_country' => 'ES',
            'shipping_tax_id' => null,
            'billing_recipient' => $user->name,
            'billing_line_1' => 'Calle de Prueba San Jerónimo 12',
            'billing_line_2' => 'Piso 3A',
            'billing_city' => 'Madrid',
            'billing_region' => 'MD',
            'billing_postal_code' => '28014',
            'billing_country' => 'ES',
            'billing_tax_id' => null,
        ];

        // 3. Crear pedido pendiente y reserva de stock
        try {
            $order = $checkoutService->createPendingOrder($cart, $customerDetails, $user->id);
        } catch (\Throwable $e) {
            $this->error("Error creando el pedido en checkout: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("✓ Pedido pendiente creado con número: {$order->number}");
        $this->line("   - Subtotal neto: " . number_format($order->subtotal_cents / 100, 2, ',', '.') . " €");
        $this->line("   - IVA: " . number_format($order->tax_cents / 100, 2, ',', '.') . " €");
        $this->line("   - Envío: " . number_format($order->shipping_cents / 100, 2, ',', '.') . " €");
        $this->line("   - Total del pedido: " . number_format($order->total_cents / 100, 2, ',', '.') . " €");

        // 4. Confirmar pago simulando webhook o retorno de pasarela
        $provider = (string) $this->option('provider');
        $simulatedReference = 'cs_test_' . Str::random(24);

        $this->info("Confirmando pago en pasarela [{$provider}] con referencia {$simulatedReference}...");

        $confirmed = $paymentProcessor->confirm(
            order: $order,
            provider: $provider,
            providerReference: $simulatedReference,
            amountCents: $order->total_cents,
            currency: $order->currency,
            metadata: [
                'simulation' => true,
                'user' => $user->email,
                'simulated_at' => now()->toIso8601String(),
            ]
        );

        if (! $confirmed) {
            $this->error('No se pudo confirmar el pago. Revisa las reservas de stock o el estado del pedido.');

            return self::FAILURE;
        }

        // 5. Verificar estado final
        $artwork->refresh();
        $order->refresh();

        $this->newLine();
        $this->info('========================================================');
        $this->info('  ¡COMPRA SIMULADA Y CONFIRMADA CON ÉXITO! ');
        $this->info('========================================================');
        $this->line(" Estado del pedido: <fg=green;options=bold>{$order->status->value}</>");
        $this->line(" Pagado el: {$order->paid_at->format('d/m/Y H:i:s')}");
        $this->line(" Stock restante de la obra: {$artwork->stock}");
        $this->line(" Obra marcada como vendida: " . ($artwork->is_sold ? '<fg=green>SÍ</>' : 'NO'));
        $this->line(" Factura: " . ($order->invoice ? "#{$order->invoice->number}" : 'Pendiente/Emitida'));
        $this->newLine();
        $this->line("Puedes revisar este pedido en:");
        $this->line(" - Área de cliente: /cuenta/pedidos/{$order->id}");
        $this->line(" - Panel de administración: /admin/orders");

        return self::SUCCESS;
    }

    private function ensureTaxAndShippingRates(): void
    {
        foreach (['original_art', 'shipping'] as $taxClass) {
            TaxRate::query()->firstOrCreate(
                [
                    'country_code' => 'ES',
                    'region_code' => null,
                    'tax_class' => $taxClass,
                ],
                [
                    'rate_basis_points' => 2100,
                    'valid_from' => now()->subYears(2)->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        $zone = ShippingZone::query()->firstOrCreate(
            ['name' => 'España Peninsular'],
            [
                'country_codes' => ['ES'],
                'region_codes' => null,
                'is_active' => true,
            ]
        );

        if ($zone->rates()->count() === 0) {
            $zone->rates()->create([
                'min_weight_grams' => 0,
                'max_weight_grams' => 50000,
                'price_cents' => 1500,
                'currency' => 'EUR',
                'is_active' => true,
            ]);
        }
    }
}

