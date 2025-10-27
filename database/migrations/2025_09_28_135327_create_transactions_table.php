<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('reference')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('transactionable');
            $table->unsignedInteger('amount')->comment('In kobo');
            $table->string('currency', 3)->default('NGN');
            $table->string('payment_gateway');
            $table->morphs('customer', 'customer_transaction_index');
            $table->string('status')->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
