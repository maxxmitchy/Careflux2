<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RedirectAuthenticatedUserController extends Controller
{
    public function pharmacy(): RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::user()->is_pharmacist) {
            return redirect()->route('filament.pharmacy.pages.dashboard');
        }

        return redirect()->route('filament.pharmacy.auth.login');
    }
}
