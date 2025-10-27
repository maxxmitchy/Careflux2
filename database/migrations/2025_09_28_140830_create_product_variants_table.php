<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_product_variants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_product_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g., "500mg (20 tablets)"');
            $table->unsignedInteger('price')->comment('Price in kobo for this variant.');
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
