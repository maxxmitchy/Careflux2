<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckPharmacyVerification;
use App\Livewire\Auth\Pharmacist\Register;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PharmacyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pharmacy')
            ->path('pharmacy')
            ->colors([
                'primary' => Color::Green,
            ])
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => view('partials.social-login-buttons', ['panel' => 'pharmacist'])->render()
            )
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => view('partials.back-to-home-link')->render()
            )
            ->profile()
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->login()
            ->passwordReset()
            ->registration(Register::class)
            ->viteTheme('resources/css/filament/pharmacy/theme.css')
            ->discoverResources(in: app_path('Filament/Pharmacy/Resources'), for: 'App\Filament\Pharmacy\Resources')
            ->discoverPages(in: app_path('Filament/Pharmacy/Pages'), for: 'App\Filament\Pharmacy\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Pharmacy/Widgets'), for: 'App\Filament\Pharmacy\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                // This is the default "Profile" link, we can keep it.
                // 'profile' => Action::make()->label('My Profile'),

                // This is our new, custom menu item.
                Action::make('back_home')
                    ->label('Back to Homepage')
                    ->icon('heroicon-o-home')
                    ->url('/'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckPharmacyVerification::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
