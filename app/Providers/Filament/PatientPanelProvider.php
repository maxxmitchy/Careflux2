<?php

namespace App\Providers\Filament;

use App\Livewire\Auth\Register;
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

class PatientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('patient')
            ->path('patient')
            ->login() // Use Filament's default login
            ->passwordReset()
            ->registration(Register::class)
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->profile() // Allow patients to manage their profile
            ->viteTheme('resources/css/filament/pharmacy/theme.css')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => view('partials.social-login-buttons', ['panel' => 'patient'])->render()
            )
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => view('partials.back-to-home-link')->render()
            )
            ->discoverResources(in: app_path('Filament/Patient/Resources'), for: 'App\Filament\Patient\Resources')
            ->discoverPages(in: app_path('Filament/Patient/Pages'), for: 'App\Filament\Patient\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Patient/Widgets'), for: 'App\Filament\Patient\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
