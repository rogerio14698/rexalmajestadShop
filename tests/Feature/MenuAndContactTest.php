<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PortfolioSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_renders_nested_subcategories_and_bio_sections(): void
    {
        $parent = Category::query()->firstOrCreate(
            ['slug' => 'obras-grafica-y-papel'],
            [
                'name' => 'Obras Gráfica y Papel',
                'position' => 2,
            ]
        );

        $child1 = Category::query()->firstOrCreate(
            ['slug' => 'dibujo'],
            [
                'name' => 'Dibujos',
                'parent_id' => $parent->id,
                'position' => 1,
            ]
        );

        $bio = PortfolioSection::query()->create([
            'heading' => 'Statement Artístico',
            'type' => 'bio',
            'position' => 1,
            'published_at' => now(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Obras Gráfica y Papel')
            ->assertSee('Dibujos')
            ->assertSee('Bio')
            ->assertSee('Statement Artístico');
    }

    public function test_contact_page_renders_with_proper_styles(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertOk()
            ->assertSee('contact-card')
            ->assertSee('contact-form')
            ->assertSee('Enviar mensaje al estudio');
    }
}
