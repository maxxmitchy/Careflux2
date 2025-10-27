<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_animations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Internal name, e.g., "Main Homepage Animation"');
            $table->string('headline');
            $table->string('subheadline');
            $table->text('description');
            $table->string('cta_text');
            $table->string('cta_url');
            $table->string('image_path');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_animations');
    }
};
