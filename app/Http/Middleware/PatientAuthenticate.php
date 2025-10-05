<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as AuthenticateMiddleware;

class PatientAuthenticate extends AuthenticateMiddleware
{
    // If you want to preserve guard-support and JSON handling,
    // extend the core Authenticate middleware and override redirectTo.
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('filament.patient.auth.login'); // adjust if route name differs
        }
    }
}
