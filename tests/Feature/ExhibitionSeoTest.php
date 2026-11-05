<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Artwork;
use App\Models\Exhibition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExhibitionSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_exhibitions_are_listed_and_visible_by_slug(): void
    {
        $published = Exhibition::query()->create([
            'title' => ['es' => 'Muestra abierta'],
            'slug' => 'muestra-abierta',
            'venue' => 'Galería',
            'city' => 'Madrid',
            'country_code' => 'es',
            'starts_at' => now()->toDateString(),
            'description' => ['es' => 'Descripción publicada.'],
            'published_at' => now()->subMinute(),
        ]);
        // Creación sin fecha asignada explícitamente se publica de inmediato con timestamp actual
        $this->assertNotNull($published->fresh()->published_at);

        $this->get(route('exhibitions.index'))
            ->assertOk()
            ->assertSee('Muestra abierta')
            ->assertSee('Publicada el '.$published->fresh()->published_at->format('d/m/Y'));
        $this->get(route('exhibitions.show', ['slug' => 'muestra-abierta']))
            ->assertOk()
            ->assertSee('Descripción publicada.')
            ->assertSee('application/ld+json');
    }

    public function test_sitemap_and_robots_contain_only_public_routes(): void
    {
        $artwork = Artwork::factory()->create([
            'category_id' => Category::where('slug', 'oleo')->value('id'),
            'slug' => 'publica',
            'published_at' => now(),
        ]);
        Artwork::factory()->create([
            'category_id' => Category::where('slug', 'oleo')->value('id'),
            'slug' => 'borrador',
            'published_at' => null,
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('artworks.show', ['category' => 'oleo', 'slug' => 'publica']), false)
            ->assertDontSee('borrador');
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin/')
            ->assertSee(route('sitemap'));
    }
}
