<?php

namespace App\Providers;

use App\Observers\InvoiceObserver;
use Src\Order\Domain\Models\Invoice;
use App\Observers\MedicationObserver;
use Illuminate\Support\ServiceProvider;
use App\Http\Responses\LoginViewResponse;
use App\Observers\MarketingAssetObserver;
use App\Observers\ScrapedProductObserver;
use App\Observers\PharmacyProductObserver;
use Src\Medication\Domain\Models\Medication;
use App\Observers\PatientInteractionObserver;
use Src\Scraping\Domain\Models\ScrapedProduct;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Patient\Domain\Models\PatientInteraction;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginViewResponseContract::class, LoginViewResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PharmacyProduct::observe(PharmacyProductObserver::class);
        MarketingAsset::observe(MarketingAssetObserver::class);
        PatientInteraction::observe(PatientInteractionObserver::class);

        ScrapedProduct::observe(ScrapedProductObserver::class);
        Medication::observe(MedicationObserver::class);

        Invoice::observe(InvoiceObserver::class);
    }
}
