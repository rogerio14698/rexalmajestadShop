<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->string('external_url')->nullable()->after('artwork_id');
            $table->string('link_label')->nullable()->after('external_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->dropColumn(['external_url', 'link_label']);
        });
    }
};
