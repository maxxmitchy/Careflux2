<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_invoice_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacy_product_id')->nullable()->comment('Null if externally sourced')->constrained('pharmacy_products')->nullOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price')->comment('Price per unit in kobo at time of sale');
            $table->unsignedInteger('total')->comment('Total for this line item in kobo');

            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->unsignedInteger('discount_amount')->default(0)->comment('Discount in kobo applied to this line item');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
