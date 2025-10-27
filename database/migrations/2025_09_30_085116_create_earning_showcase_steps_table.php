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
        Schema::create('earning_showcase_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('earning_showcase_id')->constrained()->cascadeOnDelete();
            $table->string('icon');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('points_example'); // e.g., 10, 5, 20
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earning_showcase_steps');
    }
};
