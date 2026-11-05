<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('corrects_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('type', 16);
            $table->json('issuer_snapshot');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_tax_id')->nullable();
            $table->json('billing_address_snapshot');
            $table->json('items_snapshot');
            $table->char('currency', 3);
            $table->unsignedBigInteger('net_cents');
            $table->unsignedBigInteger('tax_cents');
            $table->unsignedBigInteger('total_cents');
            $table->timestamp('issued_at');
            $table->string('correction_reason')->nullable();
            $table->timestamps();
            $table->index(['type', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_sequences');
    }
};
