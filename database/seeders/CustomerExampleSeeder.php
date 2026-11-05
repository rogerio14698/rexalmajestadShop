<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Artwork;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerExampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first();

        if (! $user) {
            return;
        }

        Address::query()->firstOrCreate(
            ['user_id' => $user->id, 'postal_code' => '28001'],
            [
                'type' => 'shipping',
                'recipient' => $user->name,
                'line_1' => 'Calle de Alcalá, 42',
                'city' => 'Madrid',
                'region_code' => 'M',
                'country_code' => 'ES',
                'is_default' => true,
            ]
        );

        $artwork = Artwork::query()->published()->forSale()->first();
        $zone = ShippingZone::query()->first();

        if (! $artwork) {
            return;
        }

        $order = Order::query()->firstOrCreate(
            ['number' => 'ORD-DEMO-2026-001'],
            [
                'user_id' => $user->id,
                'shipping_zone_id' => $zone?->id,
                'email' => $user->email,
                'status' => OrderStatus::Paid,
                'currency' => 'EUR',
                'subtotal_cents' => $artwork->price_cents,
                'shipping_cents' => 1500,
                'tax_cents' => 0,
                'total_cents' => $artwork->price_cents + 1500,
                'shipping_address_snapshot' => [
                    'full_name' => $user->name,
                    'line_one' => 'Calle de Alcalá, 42',
                    'city' => 'Madrid',
                    'postal_code' => '28001',
                    'country_code' => 'ES',
                ],
                'billing_address_snapshot' => [
                    'full_name' => $user->name,
                    'line_one' => 'Calle de Alcalá, 42',
                    'city' => 'Madrid',
                    'postal_code' => '28001',
                    'country_code' => 'ES',
                ],
                'paid_at' => now(),
            ]
        );

        OrderItem::query()->firstOrCreate(
            ['order_id' => $order->id, 'artwork_id' => $artwork->id],
            [
                'title' => $artwork->title,
                'slug' => $artwork->slug,
                'quantity' => 1,
                'unit_price_cents' => $artwork->price_cents,
                'net_cents' => $artwork->price_cents,
                'tax_cents' => 0,
                'total_cents' => $artwork->price_cents,
                'tax_rate_basis_points' => 0,
                'tax_class' => 'standard',
                'tax_country_code' => 'ES',
                'weight_grams' => $artwork->weight_grams ?? 500,
            ]
        );
    }
}
