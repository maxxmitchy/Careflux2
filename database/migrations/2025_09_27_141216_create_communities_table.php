<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_communities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('pharmacy_id')->comment('The primary pharmacy managing this community')->constrained('pharmacies')->cascadeOnDelete();
            $table->morphs('owner');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
