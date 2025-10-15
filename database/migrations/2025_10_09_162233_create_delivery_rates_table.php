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
        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->comment('Origin Pharmacy')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->comment('Destination Zone')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cost_kobo');
            $table->timestamps();
            $table->unique(['pharmacy_id', 'delivery_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_rates');
    }
};
