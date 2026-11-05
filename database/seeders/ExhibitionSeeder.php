<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use Illuminate\Database\Seeder;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = [
            [
                'slug' => 'geometria-sagrada-madrid',
                'title' => 'Geometría sagrada',
                'venue' => 'Galería La Imprenta',
                'city' => 'Madrid',
                'country_code' => 'ES',
                'starts_at' => now()->subMonths(8)->startOfMonth(),
                'ends_at' => now()->subMonths(7)->endOfMonth(),
                'description' => 'Exposición individual con la serie Zodiaco y una selección de óleos recientes: sólidos platónicos, constelaciones y retratos suspendidos.',
            ],
            [
                'slug' => 'animalia-colectiva',
                'title' => 'Animalia',
                'venue' => 'Sala de Arte Joven',
                'city' => 'Madrid',
                'country_code' => 'ES',
                'starts_at' => now()->subMonths(3)->startOfMonth(),
                'ends_at' => now()->subMonths(2)->endOfMonth(),
                'description' => 'Muestra colectiva en torno a la fauna en la pintura contemporánea, con tres óleos de la serie Retratos.',
            ],
            [
                'slug' => 'anforas-proxima-muestra',
                'title' => 'Ánforas',
                'venue' => 'Espacio Cerámica',
                'city' => 'Valencia',
                'country_code' => 'ES',
                'starts_at' => now()->addMonths(2)->startOfMonth(),
                'ends_at' => now()->addMonths(3)->endOfMonth(),
                'description' => 'Próxima muestra dedicada a la serie Grecia: la cerámica clásica reinterpretada al óleo.',
            ],
        ];

        foreach ($exhibitions as $exhibition) {
            Exhibition::query()->updateOrCreate(
                ['slug' => $exhibition['slug']],
                [
                    'title' => ['es' => $exhibition['title'], 'en' => $exhibition['title']],
                    'venue' => $exhibition['venue'],
                    'city' => $exhibition['city'],
                    'country_code' => $exhibition['country_code'],
                    'starts_at' => $exhibition['starts_at'],
                    'ends_at' => $exhibition['ends_at'],
                    'description' => ['es' => $exhibition['description'], 'en' => $exhibition['description']],
                    'published_at' => now(),
                ],
            );
        }
    }
}
