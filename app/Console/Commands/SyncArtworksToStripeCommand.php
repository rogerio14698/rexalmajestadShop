<?php

namespace App\Console\Commands;

use App\Models\Artwork;
use Illuminate\Console\Command;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class SyncArtworksToStripeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-artworks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza las obras del catálogo de Rexalmajestad con el catálogo de productos y precios en Stripe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $secret = config('cashier.secret');

        if (blank($secret)) {
            $this->error('Falta la clave STRIPE_SECRET en el archivo .env');

            return self::FAILURE;
        }

        $stripe = Cashier::stripe();
        $artworks = Artwork::query()->orderBy('id')->get();

        $this->info("Iniciando sincronización de {$artworks->count()} obras con Stripe...");

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($artworks as $artwork) {
            $title = is_array($artwork->title) ? ($artwork->title['es'] ?? reset($artwork->title)) : (string) $artwork->title;
            $description = is_array($artwork->description) ? ($artwork->description['es'] ?? reset($artwork->description)) : (string) $artwork->description;
            $priceCents = (int) $artwork->price_cents;
            $currency = strtolower(config('commerce.currency', 'eur'));

            $metadata = [
                'artwork_id' => (string) $artwork->id,
                'slug' => (string) $artwork->slug,
                'year' => (string) $artwork->year,
                'technique' => (string) $artwork->technique,
                'category' => (string) ($artwork->category?->name ?? ''),
            ];

            try {
                // Buscamos si ya existe el producto con metadata artwork_id
                $searchResult = $stripe->products->search([
                    'query' => "metadata['artwork_id']:'{$artwork->id}'",
                    'limit' => 1,
                ]);

                $product = $searchResult->data[0] ?? null;

                if (! $product) {
                    // Crear nuevo producto en Stripe
                    $product = $stripe->products->create([
                        'name' => $title,
                        'description' => \Illuminate\Support\Str::limit($description, 500),
                        'active' => (bool) $artwork->is_for_sale,
                        'metadata' => $metadata,
                    ]);

                    if ($priceCents > 0) {
                        $stripe->prices->create([
                            'product' => $product->id,
                            'unit_amount' => $priceCents,
                            'currency' => $currency,
                        ]);
                    }

                    $this->line(" <fg=green>[CREADO]</> #{$artwork->id} {$title} ({$product->id})");
                    $created++;
                } else {
                    // Actualizar producto existente
                    $stripe->products->update($product->id, [
                        'name' => $title,
                        'description' => \Illuminate\Support\Str::limit($description, 500),
                        'active' => (bool) $artwork->is_for_sale,
                        'metadata' => $metadata,
                    ]);

                    $this->line(" <fg=yellow>[ACTUALIZADO]</> #{$artwork->id} {$title} ({$product->id})");
                    $updated++;
                }
            } catch (ApiErrorException $e) {
                $this->error("Error sincronizando obra #{$artwork->id} ({$title}): {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Sincronización completada: {$created} creados, {$updated} actualizados, {$failed} errores.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

