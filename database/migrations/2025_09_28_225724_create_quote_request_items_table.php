<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_quote_request_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->morphs('productable'); // Links to ScrapedProduct, PharmacyProduct, etc.
            $table->string('status')->default('pending'); // pending, available, unavailable
            $table->unsignedInteger('negotiated_price')->nullable()->comment('Price in kobo confirmed by admin');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_items');
    }
};
