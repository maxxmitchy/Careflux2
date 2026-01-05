<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_product_id')
                ->constrained('pharmacy_products')
                ->cascadeOnDelete();

            $table->string('batch_number')->nullable()->index();
            $table->date('expiry_date')->index();
            $table->unsignedInteger('quantity')->default(0);

            // We need cost_price here to calculate the "Profit" later
            // (Selling Price - Cost Price = Profit)
            $table->unsignedInteger('cost_price')->nullable()->comment('Unit cost price in kobo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
