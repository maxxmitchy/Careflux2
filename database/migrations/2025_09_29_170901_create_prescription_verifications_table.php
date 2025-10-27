<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('medication_variant_id')->constrained('medication_variants')->cascadeOnDelete();
            $table->foreignId('verifier_id')->comment('Pharmacist who verified')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('pending')->index(); // pending, verified, completed, rejected, expired
            $table->string('reference_code')->unique(); // Initial code for patient to give pharmacist
            $table->string('final_verification_code')->nullable(); // Hashed code pharmacist gives patient

            $table->unsignedInteger('quantity_allowed')->default(1);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_verifications');
    }
};
