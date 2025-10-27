<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\Scraping\Domain\Enums\StockStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 1024);
            $table->string('search_keyword')->nullable()->index();
            $table->text('image_url')->nullable();
            $table->text('product_url')->nullable();
            $table->string('brand')->nullable()->index();
            $table->unsignedInteger('price')->comment('In kobo');
            $table->string('soundex_name', 4)->nullable()->index();
            $table->string('external_id')->comment('Unique ID from the source store');
            $table->string('stock_status')->default(StockStatus::IN_STOCK->value)->index();
            $table->boolean('is_blacklisted')->default(false)->index();
            $table->timestamps();
            $table->unique(['store_id', 'external_id']);
        });
        DB::statement('ALTER TABLE scraped_products ADD FULLTEXT fulltext_product_name (product_name)');
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_products');
    }
};
