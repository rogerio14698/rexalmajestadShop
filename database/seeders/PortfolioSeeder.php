<?php

namespace Database\Seeders;

use App\Models\Artwork;
use App\Models\PortfolioSection;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'heading' => 'Empezar por la materia',
                'body' => "Texto de muestra para editar desde el admin.\n\nTodo empieza en el taller: la tinta, el papel hecho a mano, el lienzo imprimado. Antes que un tema, hay un material que pide una imagen.",
                'artwork_slug' => 'futbolista-nocturno',
                'position' => 1,
            ],
            [
                'heading' => 'La geometría como lenguaje',
                'body' => "Texto de muestra para editar desde el admin.\n\nLos sólidos platónicos, las fases de la luna y los mapas celestes aparecen una y otra vez: son la gramática que ordena el caos de las figuras.",
                'artwork_slug' => 'banista-del-dodecaedro',
                'position' => 2,
            ],
            [
                'heading' => 'Mitología cotidiana',
                'body' => "Texto de muestra para editar desde el admin.\n\nLas ánforas griegas, los zodiacos y los retratos comparten una misma idea: tratar lo cercano (un amigo, un insecto, una fruta) con la solemnidad de un mito.",
                'artwork_slug' => 'anfora-del-pegaso',
                'position' => 3,
            ],
            [
                'heading' => 'El estudio, hoy',
                'body' => "Texto de muestra para editar desde el admin.\n\nActualmente trabajo entre el óleo de gran formato y la obra sobre papel. Si quieres ver una pieza en persona o encargar una obra, escríbeme.",
                'artwork_slug' => null,
                'position' => 4,
            ],
        ];

        foreach ($sections as $section) {
            PortfolioSection::query()->updateOrCreate(
                ['heading' => $section['heading']],
                [
                    'body' => $section['body'],
                    'artwork_id' => $section['artwork_slug']
                        ? Artwork::query()->where('slug', $section['artwork_slug'])->value('id')
                        : null,
                    'position' => $section['position'],
                    'published_at' => now(),
                ],
            );
        }
    }
}
