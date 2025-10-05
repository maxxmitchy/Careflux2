<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Infrastructure\Services\SessionCartService;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CartServiceInterface::class, SessionCartService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
