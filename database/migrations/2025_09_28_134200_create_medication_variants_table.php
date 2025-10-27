<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_medication_variants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g., "500mg (20 tablets)", "10mg (30 tablets)"');
            $table->unique(['medication_id', 'name']); // Ensure no duplicate variants for a medication
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_variants');
    }
};
