<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_medications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Lisinopril 10mg Tablet"
            $table->string('generic_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_prescription')->default(false);
            $table->string('image')->nullable()->comment('Canonical image for this drug');
            $table->string('soundex_name', 4)->nullable()->index();
            $table->string('status')->default('approved')->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
