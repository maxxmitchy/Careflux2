<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claiming_user_id')->comment('The pharmacist helping to sell')->constrained('users');
            $table->foreignId('claiming_pharmacy_id')->constrained('pharmacies');
            $table->unsignedInteger('quantity_claimed');
            $table->decimal('agreed_commission_percent', 5, 2)->default(30.00);
            $table->string('status')->default('pending_approval')->index(); // pending_approval, approved, sold, cancelled
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_claims');
    }
};
