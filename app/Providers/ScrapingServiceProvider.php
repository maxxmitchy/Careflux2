<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Scraping\Application\Actions\UpsertScrapedProductsAction;
use Src\Shared\Domain\Contracts\DelayStrategyInterface;
use Src\Shared\Domain\Contracts\UpsertActionInterface;
use Src\Shared\Infrastructure\Support\SleepDelayStrategy;

class ScrapingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        // Binding for the Delay Strategy
        $this->app->bind(
            DelayStrategyInterface::class,
            SleepDelayStrategy::class
        );

        // Binding for the Upsert Action
        // This is a likely future error, so we will fix it proactively.
        $this->app->bind(
            UpsertActionInterface::class,
            UpsertScrapedProductsAction::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
