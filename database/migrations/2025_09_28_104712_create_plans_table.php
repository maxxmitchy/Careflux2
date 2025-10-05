<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('e.g., Pharmacy - Silver Tier');
            $table->string('slug')->unique()->comment('e.g., pharmacy-silver');
            $table->string('user_type')->index()->comment('Defines who can subscribe: pharmacy, patient, etc.');
            $table->unsignedInteger('price_monthly')->comment('Price in kobo');
            $table->json('features')->comment('Defines limits, e.g., {"tasks_per_month": 100, "patient_cap": 50}');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
