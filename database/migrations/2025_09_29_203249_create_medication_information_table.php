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
        Schema::create('medication_information', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('The primary drug name, e.g., "Ozempic"');
            $table->string('generic_name')->nullable();
            $table->string('slug')->unique();

            // Core Content Fields
            $table->text('description')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('how_to_use')->nullable();

            // The "People Also Ask" field for the SEO hack
            $table->json('people_also_ask')->nullable();

            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_information');
    }
};
