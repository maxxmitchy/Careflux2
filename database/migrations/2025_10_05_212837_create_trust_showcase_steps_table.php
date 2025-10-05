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
        Schema::create('trust_showcase_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trust_showcase_id')->constrained()->cascadeOnDelete();
            $table->string('icon')->comment('The name of the Heroicon to display.');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trust_showcase_steps');
    }
};