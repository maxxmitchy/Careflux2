<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_similarity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('similar_medication_id')->constrained('medications')->cascadeOnDelete();
            $table->unique(['medication_id', 'similar_medication_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_similarity');
    }
};
