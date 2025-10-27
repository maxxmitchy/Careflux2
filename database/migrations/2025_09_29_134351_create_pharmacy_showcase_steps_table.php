<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_showcase_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_showcase_id')->constrained()->cascadeOnDelete();
            $table->string('icon');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_showcase_steps');
    }
};
