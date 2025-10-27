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
        // Pivot for linking info pages to specific pharmacy products
        Schema::create('med_info_pharmacy_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_information_id')->constrained('medication_information')->cascadeOnDelete();
            $table->foreignId('pharmacy_product_id')->constrained()->cascadeOnDelete();
            $table->unique(['medication_information_id', 'pharmacy_product_id'], 'med_info_product_unique');
        });

        // Pivot for linking info pages to other info pages (related content)
        Schema::create('related_medication_information', function (Blueprint $table) {
            $table->primary(['medication_information_id', 'related_id']);
            $table->foreignId('medication_information_id')->constrained('medication_information')->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('medication_information')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_information_pivot_tables');
    }
};
