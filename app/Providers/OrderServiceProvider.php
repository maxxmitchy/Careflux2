<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Order\Application\Actions\PlaceOrderAction;
use Src\Order\Domain\Contracts\PlaceOrderActionInterface;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PlaceOrderActionInterface::class, PlaceOrderAction::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
