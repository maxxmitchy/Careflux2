<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscribable'); // Creates `subscribable_id` and `subscribable_type`
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('payment_gateway')->comment('e.g., trial, paystack, stripe');
            $table->string('gateway_reference')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
