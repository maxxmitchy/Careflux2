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
        Schema::create('counseling_journey_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counseling_journey_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('day_to_send')->comment('e.g., 1 for Day 1, 2 for Day 2');
            $table->text('message_template');
            $table->unique(['counseling_journey_id', 'day_to_send'], 'cjs_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_journey_steps');
    }
};
