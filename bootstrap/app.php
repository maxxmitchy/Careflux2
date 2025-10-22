<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('tasks:dispatch-onboarding-followups')->hourly();
        // $schedule->command('tasks:dispatch-refill-reminders')->dailyAt('08:00');
        $schedule->command('wallet:convert-points')->monthlyOn(1, '02:00'); // Run at 2 AM on the 1st of every month
        $schedule->command('patients:schedule-followups')->hourly();
        $schedule->command('patients:assign-unassigned')->everyFiveMinutes();

        $schedule->command('scrape:nafdac-registry')->weekly()->sundays()->at('02:00'); // Run at 2 AM every Sunday
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(HandleCors::class);

        $middleware->alias([
            'patient.auth' => \App\Http\Middleware\PatientAuthenticate::class,
        ]);

        $middleware->group('api', [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // For SPA auth
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
