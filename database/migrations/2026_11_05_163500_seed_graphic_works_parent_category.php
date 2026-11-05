<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear categoría 'Obras Gráfica y Papel' si no existe
        $grafica = Category::query()->firstOrCreate(
            ['slug' => 'obras-grafica-y-papel'],
            [
                'name' => 'Obras Gráfica y Papel',
                'position' => 2,
            ]
        );

        // Ajustar posiciones de Óleo y Cerámica
        Category::query()->where('slug', 'oleo')->update(['position' => 0]);
        Category::query()->where('slug', 'ceramica')->update(['position' => 1]);

        // 2. Anidar Dibujo, Grabado y Acuarela bajo Obras Gráfica y Papel
        Category::query()->whereIn('slug', ['dibujo', 'grabado', 'acuarela'])->update([
            'parent_id' => $grafica->id,
        ]);
        Category::query()->where('slug', 'dibujo')->update(['name' => 'Dibujos', 'position' => 0]);
        Category::query()->where('slug', 'grabado')->update(['name' => 'Grabados', 'position' => 1]);
        Category::query()->where('slug', 'acuarela')->update(['name' => 'Acuarelas', 'position' => 2]);
    }

    public function down(): void
    {
        Category::query()->whereIn('slug', ['dibujo', 'grabado', 'acuarela'])->update([
            'parent_id' => null,
        ]);
    }
};
