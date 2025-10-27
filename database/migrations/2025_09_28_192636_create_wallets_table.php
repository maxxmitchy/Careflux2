<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_wallets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // Creates owner_id and owner_type
            $table->unsignedBigInteger('balance')->default(0)->comment('Balance in kobo');
            $table->timestamps();
            $table->unique(['owner_id', 'owner_type']); // Ensure an owner can only have one wallet
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
