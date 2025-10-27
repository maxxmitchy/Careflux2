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
        Schema::create('product_expiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_product_id')->constrained()->cascadeOnDelete();
            $table->date('expiry_date');
            $table->unsignedInteger('quantity');
            $table->foreignId('logged_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_expiries');
    }
};
