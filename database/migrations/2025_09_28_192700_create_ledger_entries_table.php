<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_ledger_entries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->comment('Amount in kobo (positive for credit, negative for debit)');
            $table->string('type')->index()->comment('e.g., reward, payout, purchase, refund');
            $table->string('description');
            $table->nullableMorphs('sourceable'); // The source of the transaction (e.g., a Task, an Order)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
