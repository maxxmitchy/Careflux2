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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('pharmacist_id')->nullable()->comment('Primary pharmacist user ID')->constrained('users')->nullOnDelete();
            $table->foreignId('family_head_patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();

            // Basic Information
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('location_area')->nullable();

            // Health Snapshot
            $table->boolean('takes_regular_medications')->default(false);
            $table->text('medication_list')->nullable();
            $table->json('known_health_conditions')->nullable();
            $table->string('last_health_check')->nullable();

            // Spending & Access
            $table->string('monthly_medicine_spend')->nullable();
            $table->string('usual_purchase_location')->nullable();
            $table->boolean('received_pharmacist_follow_up')->default(false);

            // Expectations & Consent
            $table->json('expectations_from_pharmacist')->nullable();
            $table->boolean('consents_to_contact')->default(false);

            $table->timestamp('last_interacted_at')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
