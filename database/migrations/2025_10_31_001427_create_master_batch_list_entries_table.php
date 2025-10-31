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
        Schema::create('master_batch_list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_batch_list_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->index();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            // We can add a unique constraint to prevent duplicate batches in the same list
            $table->unique(['master_batch_list_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_batch_list_entries');
    }
};
