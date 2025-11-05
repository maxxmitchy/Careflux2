<?php

namespace App\Providers;

use App\Http\Responses\LoginViewResponse;
use App\Observers\InvoiceObserver;
use App\Observers\MarketingAssetObserver;
use App\Observers\MedicationObserver;
use App\Observers\PatientInteractionObserver;
use App\Observers\PharmacyProductObserver;
use App\Observers\ScrapedProductObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Medication\Domain\Models\Medication;
use Src\Order\Domain\Models\Invoice;
use Src\Patient\Domain\Models\PatientInteraction;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Scraping\Domain\Models\ScrapedProduct;

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
