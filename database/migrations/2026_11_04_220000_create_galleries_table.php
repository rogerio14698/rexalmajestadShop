<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->foreignId('gallery_id')
                ->nullable()
                ->after('series')
                ->constrained('galleries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gallery_id');
        });

        Schema::dropIfExists('galleries');
    }
};
