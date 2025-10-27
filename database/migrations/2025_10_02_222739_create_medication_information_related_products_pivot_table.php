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
        Schema::create('med_info_related_product', function (Blueprint $table) {
            $table->id();
            // This links to the "parent" information page
            $table->foreignId('medication_information_id')->constrained('medication_information')->cascadeOnDelete();

            // This links to the "child" or "featured" pharmacy product
            $table->foreignId('pharmacy_product_id')->constrained('pharmacy_products')->cascadeOnDelete();

            // Prevent duplicate entries
            $table->unique(['medication_information_id', 'pharmacy_product_id'], 'med_info_related_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('med_info_related_product');
    }
};
