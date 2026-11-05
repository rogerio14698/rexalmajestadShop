<?php

namespace Database\Factories;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artwork>
 */
class ArtworkFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ['es' => Str::title($title), 'en' => Str::title($title)],
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'category_id' => fn () => Category::query()->inRandomOrder()->value('id') ?? Category::factory()->create()->getKey(),
            'year' => fake()->numberBetween(2018, (int) now()->format('Y')),
            'technique' => 'Técnica mixta',
            'height_cm' => fake()->randomFloat(2, 15, 120),
            'width_cm' => fake()->randomFloat(2, 15, 120),
            'weight_grams' => fake()->numberBetween(200, 4000),
            'description' => ['es' => 'Obra de ejemplo ficticia para pruebas.', 'en' => 'Fictional sample artwork for testing.'],
            'price_cents' => fake()->numberBetween(5000, 250000),
            'stock' => 1,
            'is_for_sale' => true,
            'is_sold' => false,
            'tax_class' => 'original_art',
            'ask_shipping' => false,
            'published_at' => now(),
        ];
    }
}
