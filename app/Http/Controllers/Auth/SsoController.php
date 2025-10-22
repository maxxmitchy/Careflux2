<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Src\Shared\Domain\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SsoController extends Controller
{
    /**
     * Handle the single sign-on login request.
     * This controller executes within the Core API application context.
     */
    public function login(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        $redirectUrl = $request->query('redirect_url') ? base64_decode($request->query('redirect_url')) : null;

        if (!$token) {
            Log::warning('SSO Login Attempt: No token provided.');
            abort(403, 'Invalid SSO token.');
        }

        $userId = Cache::pull("sso:{$token}");

        if (!$userId) {
            Log::warning('SSO Login Attempt: Expired or invalid token used.', ['token' => $token]);
            abort(403, 'SSO token is invalid or has expired.');
        }

        // We are finding the user in the Core API's "users" table.
        $user = User::find($userId);
        if (!$user) {
            Log::error("SSO Login Failed: User ID {$userId} found in cache but not in database.");
            abort(403, 'Invalid user associated with SSO token.');
        }

        // Log the user into the Core API's session.
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        // Redirect to the intended destination within the Core API domain.
        $finalRedirect = $redirectUrl ?? route('filament.patient.pages.dashboard');

        return redirect()->to($finalRedirect);
    }
}
