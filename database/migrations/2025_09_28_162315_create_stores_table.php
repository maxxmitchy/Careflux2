<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->string('brand')->nullable()->index();
            $table->string('logo')->nullable();
            $table->string('platform')->nullable()->index()->comment('e.g., amazon, shopify, etc.');
            $table->boolean('priority')->default(false)->index();

            // Foreign Keys
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');

            // --- New Strategy & Configuration Columns ---
            $table->string('datasource_strategy_key')->nullable()
                ->comment('Key mapping to config/datasources.php strategies (e.g., standard_scrape, bestbuy_api).');

            $table->string('search_url_template')->nullable()
                ->comment('URL template with {query} placeholder for simple searches.');

            $table->string('url_builder_strategy')->nullable()
                ->comment('FQCN of a class implementing UrlBuilderStrategyInterface for complex cases.');

            // This column will hold the fully-qualified class name of the scraper.
            // e.g., 'App\Scrapers\WalmartScraper'
            $table->string('scraper_class')->nullable()
                ->comment('The FQCN of the scraper implementation for this store.');

            // Examples: '?', '&'
            $table->string('paginate_type', 10)
                ->nullable()
                ->comment('The separator for pagination params (e.g., ? or &).');

            // Examples: 'page', 'p', 'start'
            $table->string('paginate_keyword', 50)
                ->nullable()
                ->comment('The query parameter key for the page number.');

            $table->unsignedSmallInteger('page_size')
                ->nullable()
                ->comment('Number of items per page, for offset-based pagination.');

            $table->unsignedTinyInteger('max_retry_attempts')->nullable()
                ->comment('Per-store override for max retry attempts.');

            $table->unsignedInteger('retry_delay_ms')->nullable()
                ->comment('Per-store override for the base retry delay in milliseconds.');

            $table->unsignedSmallInteger('timeout_seconds')->nullable()
                ->comment('Per-store override for the process timeout in seconds.');

            // Store-wide scraper config
            $table->boolean('requires_javascript')->default(false);

            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
