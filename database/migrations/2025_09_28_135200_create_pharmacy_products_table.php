<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_pharmacy_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Pharmacist who listed it')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_variant_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->unsignedInteger('price')->comment('Price in kobo for this pharmacy');
            $table->unsignedInteger('stock')->default(0);
            $table->string('verification_status')->default('unverified');
            $table->string('nafdac_number')->nullable();
            $table->timestamps();

            $table->unique(['pharmacy_id', 'medication_variant_id']);
            $table->unique(['pharmacy_id', 'slug']); // Ensure slug is unique per pharmacy
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_products');
    }
};
