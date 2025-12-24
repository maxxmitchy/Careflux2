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
        Schema::table('patients', function (Blueprint $table) {
            // First, ensure the existing column is nullable.
            // This is safe to run even if it's already nullable.
            $table->string('phone')->nullable()->change();

            // Add the unique constraint.
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // It's good practice to define the rollback logic.
            $table->dropUnique(['phone']);
        });
    }
};
