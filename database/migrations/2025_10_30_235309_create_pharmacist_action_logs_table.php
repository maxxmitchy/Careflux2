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
        Schema::create('pharmacist_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('The pharmacist who performed the action')->constrained('users')->cascadeOnDelete();

            // The direct subject of the action (e.g., a Task, a Patient, an Order)
            $table->nullableMorphs('subjectable');

            $table->string('action_type')->index()->comment('A unique key for the action, e.g., PRODUCT_RECALL_CONFIRMED');
            $table->text('description')->nullable()->comment('A human-readable summary of the action.');
            $table->json('metadata')->nullable()->comment('Rich, structured data about the action.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacist_action_logs');
    }
};
