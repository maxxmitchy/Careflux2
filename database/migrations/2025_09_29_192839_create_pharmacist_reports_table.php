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
        Schema::create('pharmacist_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('The pharmacist who submitted the report')->constrained()->cascadeOnDelete();
            $table->date('week_ending_date');

            // Feedback
            $table->text('biggest_win')->nullable();
            $table->text('biggest_challenge')->nullable();
            $table->text('at_risk_patients')->nullable();
            $table->text('support_needed')->nullable();

            $table->timestamps();

            // A pharmacist should only submit one report per week
            $table->unique(['user_id', 'week_ending_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacist_reports');
    }
};
