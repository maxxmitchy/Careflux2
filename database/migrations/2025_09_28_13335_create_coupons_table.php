<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->comment('Pharmacist who created it')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->foreignId('approved_by_user_id')->nullable()->comment('Manager who approved it')->constrained('users')->nullOnDelete();
            $table->morphs('productable');
            $table->string('code')->unique();
            $table->unsignedInteger('discount_amount')->comment('Discount in kobo');
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->timestamp('expires_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
