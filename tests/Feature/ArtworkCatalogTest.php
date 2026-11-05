<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_listing_orders_newer_years_first_and_shows_sold_works(): void
    {
        $older = Artwork::factory()->create([
            'title' => ['es' => 'Lienzo antiguo'],
            'category_id' => Category::where('slug', 'oleo')->value('id'),
            'year' => 2022,
            'slug' => 'lienzo-antiguo',
        ]);
        $sold = Artwork::factory()->create([
            'title' => ['es' => 'Lienzo vendido'],
            'category_id' => Category::where('slug', 'oleo')->value('id'),
            'year' => 2025,
            'slug' => 'lienzo-vendido',
            'is_sold' => true,
            'stock' => 0,
        ]);

        $response = $this->get(route('artworks.category', ['category' => 'oleo']));

        $response->assertOk()
            ->assertSeeInOrder(['Lienzo vendido', 'Lienzo antiguo'])
            ->assertSee('Vendido');
    }

    public function test_work_with_no_stock_cannot_be_purchased(): void
    {
        $artwork = Artwork::factory()->create([
            'category_id' => Category::where('slug', 'dibujo')->value('id'),
            'slug' => 'sin-stock',
            'stock' => 0,
        ]);

        $this->assertFalse($artwork->canPurchase());

        $this->get(route('artworks.show', ['category' => 'dibujo', 'slug' => 'sin-stock']))
            ->assertOk()
            ->assertDontSee('Añadir al carrito');
    }

    public function test_shop_lists_only_works_marked_for_sale(): void
    {
        Artwork::factory()->create([
            'title' => ['es' => 'Disponible'],
            'slug' => 'disponible',
            'is_for_sale' => true,
        ]);
        Artwork::factory()->create([
            'title' => ['es' => 'Solo portfolio'],
            'slug' => 'solo-portfolio',
            'is_for_sale' => false,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Disponible')
            ->assertDontSee('Solo portfolio');
    }
}
