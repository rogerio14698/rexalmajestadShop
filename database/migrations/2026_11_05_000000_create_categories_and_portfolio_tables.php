<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Categorías existentes (antes un enum fijo), ahora editables desde el admin.
        $now = now();
        $position = 0;

        foreach (['oleo' => 'Óleo', 'dibujo' => 'Dibujo', 'grabado' => 'Grabado', 'acuarela' => 'Acuarela', 'ceramica' => 'Cerámica'] as $slug => $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'slug' => $slug,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('artworks', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('slug')
                ->constrained('categories')
                ->nullOnDelete()
                ->index();
        });

        foreach (DB::table('categories')->get() as $category) {
            DB::table('artworks')->where('category', $category->slug)->update(['category_id' => $category->id]);
        }

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['category', 'year', 'created_at']);
            $table->dropColumn('category');
        });

        Schema::create('portfolio_sections', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->text('body')->nullable();
            $table->foreignId('artwork_id')->nullable()->constrained('artworks')->nullOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_sections');

        Schema::table('artworks', function (Blueprint $table) {
            $table->string('category', 24)->nullable()->index();
        });

        foreach (DB::table('categories')->get() as $category) {
            DB::table('artworks')->where('category_id', $category->id)->update(['category' => $category->slug]);
        }

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
