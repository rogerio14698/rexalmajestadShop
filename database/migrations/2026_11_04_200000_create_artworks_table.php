<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 24)->index();
            $table->unsignedSmallInteger('year')->index();
            $table->string('technique');
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('depth_cm', 8, 2)->nullable();
            $table->unsignedInteger('weight_grams')->default(0);
            $table->string('series')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_cents')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_for_sale')->default(false)->index();
            $table->boolean('is_sold')->default(false)->index();
            $table->string('tax_class', 40)->default('original_art');
            $table->boolean('ask_shipping')->default(false);
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['category', 'year', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
