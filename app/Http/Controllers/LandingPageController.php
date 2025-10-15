<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Benefit;
use App\Models\DeliveryAnimation;
use App\Models\EarningShowcase;
use App\Models\Feature;
use App\Models\Partner;
use App\Models\PharmacyShowcase;
use App\Models\TrustShowcase;
use App\Settings\GeneralSettings;
use Src\Marketing\Domain\Models\MarketingAsset;

class LandingPageController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        return view('welcome', [
            'announcement' => Announcement::where('is_active', true)->first(),
            'partners' => Partner::where('is_active', true)->orderBy('sort_order')->get(),
            'features' => Feature::where('is_active', true)->orderBy('sort_order')->get(),
            'featuredPackages' => MarketingAsset::query()
                ->where('is_active', true)
                ->where('type', 'wishlist')
                ->orWhere('type', 'care_package')
                ->with(['pharmacy', 'user'])
                ->inRandomOrder()->limit(4)->get(),
            'benefits' => Benefit::where('is_active', true)->orderBy('sort_order')->take(3)->get(),
            'deliveryAnimation' => DeliveryAnimation::where('is_active', true)->with('steps')->first(),
            'pharmacyShowcase' => PharmacyShowcase::where('is_active', true)->with('steps')->first(),
            'earningShowcase' => EarningShowcase::where('is_active', true)->with('steps')->first(),
            'trustShowcase' => TrustShowcase::where('is_active', true)->with('steps')->first(),

            'title' => 'Your Personal Pharmacist, Always on Call',
            'description' => 'Careflux connects you with a dedicated pharmacist for proactive follow-ups, medication management, and price comparisons across trusted Nigerian pharmacies.',
            'ogImage' => $settings->site_og_image ? asset('storage/'.$settings->site_og_image) : null,
        ]);
    }
}
