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
        Schema::create('product_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->comment('Admin who created it')->constrained('users');
            $table->string('type')->index()->comment('e.g., recall, batch_verification');
            $table->string('severity')->default('high')->comment('e.g., low, medium, high, critical');
            $table->string('title');
            $table->text('instructions');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_alerts');
    }
};
