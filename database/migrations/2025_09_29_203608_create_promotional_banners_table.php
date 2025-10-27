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
        Schema::create('promotional_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text_content');
            $table->string('button_text');
            $table->string('button_url');
            $table->json('images');
            $table->boolean('is_active')->default(false)->index();
            $table->string('placement')->index()->comment('e.g., search_results, browse_page');
            $table->unsignedInteger('display_after_item')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotional_banners');
    }
};
