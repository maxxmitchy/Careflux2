<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Store\Domain\Contracts\StoreRepositoryInterface;
use Src\Store\Infrastructure\Repositories\EloquentStoreRepository;

class StoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(StoreRepositoryInterface::class, EloquentStoreRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
