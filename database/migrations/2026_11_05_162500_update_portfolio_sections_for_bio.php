<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->string('type', 40)->default('general')->after('id')->index();
            $table->string('subtitle')->nullable()->after('heading');
            $table->string('year_label', 60)->nullable()->after('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->dropColumn(['type', 'subtitle', 'year_label']);
        });
    }
};
