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
        Schema::create('medication_counseling_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('category')->default('general')->comment('e.g., general, warning, interaction');
            $table->text('point_text')->comment('The core advice, e.g., "Take with a full glass of water."');
            $table->text('message_template')->comment('The pre-written message for the pharmacist to send.');
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_counselling_points');
    }
};
