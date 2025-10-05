<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('e.g., PATIENT_FOLLOW_UP_48HR');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('points');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->foreignId('pharmacy_id')->nullable()->comment('Links custom tasks to a pharmacy')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_definitions');
    }
};
