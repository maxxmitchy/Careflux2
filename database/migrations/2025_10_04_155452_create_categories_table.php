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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // For parent-child relationships (self-referencing)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnDelete();

            // Business key
            $table->string('category_code', 10)->unique()->comment('Short, unique code (e.g., ELEC, TOYS)');

            // Human-readable category name
            $table->string('name')->comment('e.g., Electronics, Clothing & Apparel');

            // Unique slug for SEO/URL
            $table->string('slug')->unique();

            $table->string('icon')->nullable()->comment('Heroicon name');

            // Optional longer description
            $table->text('description')->nullable();

            // Visibility & sorting
            $table->boolean('is_visible')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
