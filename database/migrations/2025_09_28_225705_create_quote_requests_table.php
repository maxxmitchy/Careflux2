<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_quote_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->string('patient_name'); // Denormalized for quick access
            $table->string('patient_phone'); // Denormalized for quick access
            $table->string('status')->default('pending')->index(); // e.g., pending, available, completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
