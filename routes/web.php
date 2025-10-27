<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\LandingPageController;
use App\Livewire\Auth\CompleteSocialProfile;
use App\Livewire\PartnerDetailPage;
use App\Livewire\PrescriptionVerificationPage;
use App\Livewire\ProductDetailPage;
use App\Livewire\PublicProductSearch;
use App\Livewire\QuestionnaireForm;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\ShoppableWishlistPage;
use App\Livewire\TrackRequestQuote;
use App\Livewire\WishlistIndexPage;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', LandingPageController::class)->name('landing');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::get('/q/{invitation:token}', QuestionnaireForm::class)->name('questionnaire.show');

Route::get('/search', PublicProductSearch::class)->name('public.products');
// The product detail route will need to be created when we build that page.
Route::get('/product/{identifier}', ProductDetailPage::class)->name('public.product.detail');

Route::get('/wishlists', WishlistIndexPage::class)->name('public.wishlists');
Route::get('/wishlist/{marketingAsset:slug}', ShoppableWishlistPage::class)->name('public.wishlist');

Route::get('cart', \App\Livewire\CartPage::class)->name('cart');

Route::get('checkout', \App\Livewire\CheckoutPage::class)->name('checkout');

Route::get('payment/{reference}', \App\Livewire\PaymentPage::class)->name('payment.page');

Route::get('products', \App\Livewire\BrowseProductsPage::class)->name('products.browse');

Route::get('/auth/{provider}/redirect/{panel}', [OAuthController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback'])->name('auth.social.callback');

// The "Complete Profile" route
Route::get('/auth/complete-profile', CompleteSocialProfile::class)
    ->middleware('auth')->name('auth.complete-profile');

Route::get('/track-request/{quoteRequest}', TrackRequestQuote::class)->name('track.request');

Route::get('/prescription/verify/{pharmacyProduct:slug}', PrescriptionVerificationPage::class)
    ->middleware('patient.auth')
    ->name('prescription.verify');

Route::get('/partner/{pharmacyId}', PartnerDetailPage::class)->name('public.partner.detail');

require __DIR__.'/auth.php';
