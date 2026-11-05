<?php

namespace Database\Seeders;

use App\Models\Artwork;
use App\Models\Category;
use App\Models\Gallery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea las galerías temáticas y sus obras usando las fotografías reales
 * de la carpeta «Rexalmajestad web/Rexalmajestad web».
 */
class GalleryArtworkSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '1024M');

        $sourceDir = base_path('Rexalmajestad web'.DIRECTORY_SEPARATOR.'Rexalmajestad web');

        $categoryIds = Category::query()->pluck('id', 'slug');

        foreach ($this->galleries() as $galleryData) {
            $gallery = Gallery::query()->updateOrCreate(
                ['slug' => Str::slug($galleryData['name'])],
                [
                    'name' => $galleryData['name'],
                    'description' => $galleryData['description'],
                    'published_at' => now(),
                ],
            );

            foreach ($galleryData['artworks'] as $data) {
                $slug = Str::slug($data['title']);

                $artwork = Artwork::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => ['es' => $data['title'], 'en' => $data['title']],
                        'category_id' => $categoryIds[$data['category']] ?? null,
                        'year' => $data['year'],
                        'technique' => $data['technique'],
                        'height_cm' => $data['height'] ?? null,
                        'width_cm' => $data['width'] ?? null,
                        'weight_grams' => $data['weight'] ?? 800,
                        'series' => $galleryData['name'],
                        'gallery_id' => $gallery->getKey(),
                        'description' => [
                            'es' => $data['description'] ?? $galleryData['description'],
                            'en' => $data['description'] ?? $galleryData['description'],
                        ],
                        'price_cents' => $data['price'],
                        'stock' => 1,
                        'is_for_sale' => $data['for_sale'] ?? true,
                        'is_sold' => $data['sold'] ?? false,
                        'tax_class' => 'original_art',
                        'ask_shipping' => $data['ask_shipping'] ?? false,
                        'published_at' => now(),
                    ],
                );

                $this->attachImage($artwork, $sourceDir, $data['file']);
            }
        }
    }

    private function attachImage(Artwork $artwork, string $sourceDir, string $file): void
    {
        if ($artwork->getMedia('artwork')->isNotEmpty()) {
            return;
        }

        $path = $this->resolvePath($sourceDir, $file);

        if ($path === null) {
            $this->command?->warn("Imagen no encontrada: {$file}");

            return;
        }

        try {
            $artwork->addMedia($path)
                ->preservingOriginal()
                ->withCustomProperties(['alt' => $artwork->getTranslation('title', 'es').', obra de Rexalmajestad'])
                ->toMediaCollection('artwork');
        } catch (\Throwable $exception) {
            $this->command?->warn("No se pudo importar {$file}: {$exception->getMessage()}");
        }
    }

    /**
     * Localiza el archivo aunque su nombre use otra normalización Unicode
     * (p. ej. acentos NFD de archivos copiados desde macOS).
     */
    private function resolvePath(string $sourceDir, string $file): ?string
    {
        $path = $sourceDir.DIRECTORY_SEPARATOR.$file;

        if (is_file($path)) {
            return $path;
        }

        $wanted = $this->normalizeName($file);

        foreach (scandir($sourceDir) ?: [] as $candidate) {
            if ($this->normalizeName($candidate) === $wanted) {
                return $sourceDir.DIRECTORY_SEPARATOR.$candidate;
            }
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        // Elimina marcas diacríticas combinantes (forma NFD) y sustituye vocales acentuadas (forma NFC).
        $name = preg_replace('/[\x{0300}-\x{036f}]/u', '', $name) ?? $name;

        return strtr($name, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    /**
     * @return array<int, array{name: string, description: string, artworks: array<int, array<string, mixed>>}>
     */
    private function galleries(): array
    {
        return [
            [
                'name' => 'Grecia',
                'description' => 'Ánforas y vasos de la cerámica griega reinterpretados al óleo: mitos clásicos con calaveras, unicornios y victorias aladas.',
                'artworks' => [
                    ['file' => 'grecia.JPG', 'title' => 'Crátera del banquete', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 22, 'price' => 48000],
                    ['file' => 'grecia1.JPG', 'title' => 'Vaso de las calaveras', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 15, 'price' => 42000],
                    ['file' => 'grecia2.JPG', 'title' => 'Ánfora de la victoria alada', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 15, 'price' => 42000],
                    ['file' => 'grecia3.JPG', 'title' => 'Vaso del unicornio', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 35, 'width' => 25, 'price' => 46000],
                    ['file' => 'grecia4.JPG', 'title' => 'Ánfora del jardín', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 22, 'price' => 44000],
                    ['file' => 'grecia5.JPG', 'title' => 'Ánfora del mar', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 22, 'price' => 44000],
                    ['file' => 'grecia6.JPG', 'title' => 'Ánfora del ángel', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 40, 'width' => 22, 'price' => 52000],
                    ['file' => 'grecia7.JPG', 'title' => 'Lécito de Atenea', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 22, 'price' => 46000],
                    ['file' => 'grecia8.JPG', 'title' => 'Ánfora del Pegaso', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 33, 'width' => 22, 'price' => 46000],
                ],
            ],
            [
                'name' => 'Zodiaco',
                'description' => 'Serie de tintas sobre papel dedicada a los signos del zodiaco: sólidos platónicos, fases lunares y constelaciones a plumilla.',
                'artworks' => [
                    ['file' => 'zodiacoAries.jpg', 'title' => 'Zodiaco: Aries', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoTauro.jpg', 'title' => 'Zodiaco: Tauro', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoGeminis.jpg', 'title' => 'Zodiaco: Géminis', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoCancer.jpg', 'title' => 'Zodiaco: Cáncer', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoLeo.jpg', 'title' => 'Zodiaco: Leo', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000, 'sold' => true],
                    ['file' => 'zodiacoLibra.jpg', 'title' => 'Zodiaco: Libra', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoEscorpio.jpg', 'title' => 'Zodiaco: Escorpio', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoSagitario.jpg', 'title' => 'Zodiaco: Sagitario', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoCapricornio.jpg', 'title' => 'Zodiaco: Capricornio', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoAcuario.jpg', 'title' => 'Zodiaco: Acuario', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                    ['file' => 'zodiacoPiscis.jpg', 'title' => 'Zodiaco: Piscis', 'category' => 'dibujo', 'year' => 2024, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 18000],
                ],
            ],
            [
                'name' => 'Tinta y cosmos',
                'description' => 'Ilustraciones a tinta y acuarela donde satélites, escarabajos y flores comparten un desierto geométrico.',
                'artworks' => [
                    ['file' => 'Ilustración_sin_título.JPG', 'title' => 'Astronauta entre garzas', 'category' => 'dibujo', 'year' => 2023, 'technique' => 'Ilustración digital', 'height' => 29.7, 'width' => 42, 'weight' => 150, 'price' => 15000],
                    ['file' => 'Ilustración_sin_título-4.jpg', 'title' => 'Diente de león y satélite', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta y acuarela sobre papel', 'height' => 42, 'width' => 29.7, 'weight' => 180, 'price' => 22000],
                    ['file' => 'Ilustración_sin_título-9.jpg', 'title' => 'Sputnik y escarabajo', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta y acuarela sobre papel', 'height' => 42, 'width' => 29.7, 'weight' => 180, 'price' => 22000],
                    ['file' => 'Ilustración_sin_título-10.jpg', 'title' => 'Soyuz sobre el desierto', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta y acuarela sobre papel', 'height' => 42, 'width' => 29.7, 'weight' => 180, 'price' => 22000],
                    ['file' => 'Ilustración_sin_título-11.jpg', 'title' => 'Caballo y sombra atómica', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Ilustración digital', 'height' => 42, 'width' => 29.7, 'weight' => 150, 'price' => 15000],
                    ['file' => 'Ilustración_sin_título-16.jpg', 'title' => 'Crisantemo y ciervo volante', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta y acuarela sobre papel', 'height' => 42, 'width' => 29.7, 'weight' => 180, 'price' => 22000],
                    ['file' => 'ilustracion1.JPG', 'title' => 'Tinta I', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 12000],
                    ['file' => 'ilustracion2.JPG', 'title' => 'Tinta II', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 12000],
                    ['file' => 'ilustracion3.JPG', 'title' => 'Tinta III', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 12000],
                    ['file' => 'ilustracion4.JPG', 'title' => 'Tinta IV', 'category' => 'dibujo', 'year' => 2025, 'technique' => 'Tinta sobre papel', 'height' => 29.7, 'width' => 21, 'weight' => 120, 'price' => 12000],
                ],
            ],
            [
                'name' => 'Retratos',
                'description' => 'Retratos al óleo de atmósfera surrealista: cuerpos, cráneos, esferas y animales suspendidos en escenarios imposibles.',
                'artworks' => [
                    ['file' => 'oleo.JPG', 'title' => 'Retrato con cordero', 'category' => 'oleo', 'year' => 2023, 'technique' => 'Óleo sobre lienzo', 'height' => 116, 'width' => 89, 'weight' => 4000, 'price' => 180000, 'ask_shipping' => true],
                    ['file' => 'oleo1.JPEG', 'title' => 'Cráneo y ratón', 'category' => 'oleo', 'year' => 2023, 'technique' => 'Óleo sobre lienzo', 'height' => 61, 'width' => 50, 'weight' => 2000, 'price' => 90000],
                    ['file' => 'oleo2.jpg', 'title' => 'Retrato con esferas negras', 'category' => 'oleo', 'year' => 2022, 'technique' => 'Óleo sobre lienzo', 'height' => 100, 'width' => 81, 'weight' => 3500, 'price' => 140000, 'sold' => true],
                    ['file' => 'oleo7.jpg', 'title' => 'Retrato con Sputnik', 'category' => 'oleo', 'year' => 2022, 'technique' => 'Óleo sobre lienzo, enmarcado', 'height' => 120, 'width' => 85, 'weight' => 6000, 'price' => 190000, 'ask_shipping' => true],
                    ['file' => 'oleo9.jpg', 'title' => 'Reliquia', 'category' => 'oleo', 'year' => 2024, 'technique' => 'Óleo sobre lienzo, marco recuperado', 'height' => 30, 'width' => 21, 'weight' => 900, 'price' => 38000],
                    ['file' => 'oleo11.JPEG', 'title' => 'Omnia vincit amor', 'category' => 'oleo', 'year' => 2024, 'technique' => 'Óleo sobre lienzo', 'height' => 80, 'width' => 60, 'weight' => 2500, 'price' => 120000],
                    ['file' => 'oleo12.JPEG', 'title' => 'Liebre y retrato invertido', 'category' => 'oleo', 'year' => 2024, 'technique' => 'Óleo sobre lienzo', 'height' => 73, 'width' => 92, 'weight' => 3000, 'price' => 130000],
                    ['file' => 'oleo13.JPEG', 'title' => 'Sin título XIII', 'category' => 'oleo', 'year' => 2024, 'technique' => 'Óleo sobre lienzo', 'height' => 73, 'width' => 60, 'weight' => 2500, 'price' => 95000],
                    ['file' => 'oleo14.JPEG', 'title' => 'Ab imo pectore', 'category' => 'oleo', 'year' => 2024, 'technique' => 'Óleo sobre lienzo', 'height' => 73, 'width' => 92, 'weight' => 3000, 'price' => 130000],
                    ['file' => 'oleo18.JPG', 'title' => 'Sin título XVIII', 'category' => 'oleo', 'year' => 2025, 'technique' => 'Óleo sobre lienzo', 'height' => 65, 'width' => 54, 'weight' => 2200, 'price' => 85000],
                    ['file' => 'FullSizeRender.jpg', 'title' => 'Bañista del dodecaedro', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Óleo sobre lienzo', 'height' => 100, 'width' => 100, 'weight' => 4000, 'price' => 160000],
                    ['file' => 'IMG_0004.JPG', 'title' => 'Ícaro en reposo', 'category' => 'oleo', 'year' => 2025, 'technique' => 'Acrílico sobre lienzo', 'height' => 80, 'width' => 40, 'weight' => 1800, 'price' => 70000],
                    ['file' => 'IMG_1406.JPG', 'title' => 'Efebo con zapatillas rojas', 'category' => 'oleo', 'year' => 2025, 'technique' => 'Óleo sobre lienzo', 'height' => 100, 'width' => 100, 'weight' => 4000, 'price' => 150000],
                    ['file' => 'Retrato de la ciencia y renacimiento del arte antropocéntric.JPEG', 'title' => 'Retrato de la ciencia y renacimiento del arte antropocéntrico', 'category' => 'oleo', 'year' => 2023, 'technique' => 'Óleo y pan de oro sobre lienzo', 'height' => 150, 'width' => 100, 'weight' => 7000, 'price' => 320000, 'ask_shipping' => true],
                ],
            ],
            [
                'name' => 'Bodegones del estudio',
                'description' => 'Pequeños bodegones surrealistas: mesas doradas, abejas, granadas y piedras que miran sobre mares azules.',
                'artworks' => [
                    ['file' => 'IMG_0066.JPG', 'title' => 'Flor con ojo y abeja', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Acrílico sobre tabla', 'height' => 35, 'width' => 25, 'weight' => 600, 'price' => 32000],
                    ['file' => 'IMG_0067.JPG', 'title' => 'Granada vigía', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Acrílico sobre tabla', 'height' => 35, 'width' => 25, 'weight' => 600, 'price' => 32000],
                    ['file' => 'IMG_0068.JPG', 'title' => 'La piedra que mira', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Acrílico sobre tabla', 'height' => 35, 'width' => 25, 'weight' => 600, 'price' => 32000],
                    ['file' => 'oleo10.JPG', 'title' => 'Roca con ojo y abeja', 'category' => 'oleo', 'year' => 2026, 'technique' => 'Acrílico sobre tabla', 'height' => 35, 'width' => 25, 'weight' => 600, 'price' => 32000],
                ],
            ],
            [
                'name' => 'Acuarelas',
                'description' => 'Acuarelas sobre papel: cuerpos y diagramas científicos conviven con poliedros dorados.',
                'artworks' => [
                    ['file' => 'oleo5.jpg', 'title' => 'Ciclo CNO', 'category' => 'acuarela', 'year' => 2025, 'technique' => 'Acuarela y tinta sobre papel', 'height' => 42, 'width' => 29.7, 'weight' => 200, 'price' => 26000],
                    ['file' => 'oleo6.jpg', 'title' => 'Abeja e icosaedro', 'category' => 'acuarela', 'year' => 2025, 'technique' => 'Acuarela sobre papel', 'height' => 29.7, 'width' => 42, 'weight' => 200, 'price' => 26000],
                ],
            ],
            [
                'name' => 'Grabado',
                'description' => 'Linograbados estampados a mano sobre papel artesanal.',
                'artworks' => [
                    ['file' => 'imprenta1.jpg', 'title' => 'Futbolista nocturno', 'category' => 'grabado', 'year' => 2025, 'technique' => 'Linograbado sobre papel hecho a mano', 'height' => 38, 'width' => 28, 'weight' => 150, 'price' => 14000],
                    ['file' => 'imprenta2.JPG', 'title' => 'Prueba de taller', 'category' => 'grabado', 'year' => 2025, 'technique' => 'Linograbado sobre papel hecho a mano', 'height' => 38, 'width' => 28, 'weight' => 150, 'price' => 14000],
                ],
            ],
        ];
    }
}
