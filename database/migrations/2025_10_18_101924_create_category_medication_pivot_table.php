<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old pivot if it exists
        Schema::dropIfExists('category_pharmacy_product');

        // Create the new, correct pivot table
        Schema::create('category_medication', function (Blueprint $table) {
            $table->primary(['category_id', 'medication_id']);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_medication');
        // You could recreate the old table here if you need a full rollback path
    }
};
