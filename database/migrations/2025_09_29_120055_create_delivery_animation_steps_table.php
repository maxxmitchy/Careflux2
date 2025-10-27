<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_animation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_animation_id')->constrained()->cascadeOnDelete();
            $table->string('status_text');
            $table->string('location_text');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_animation_steps');
    }
};
