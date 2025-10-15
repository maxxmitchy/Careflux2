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
        Schema::create('trust_showcases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Internal name for easy identification, e.g., "Homepage NAFDAC Animation"');
            $table->string('headline');
            $table->string('subheadline');
            $table->text('description');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trust_showcases');
    }
};
