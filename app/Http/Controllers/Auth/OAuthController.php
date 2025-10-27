<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Src\Shared\Domain\Models\User;

class OAuthController extends Controller
{
    public function redirect(string $provider, string $panel): RedirectResponse
    {
        session(['oauth_intended_panel' => $panel]);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('landing')->with('error', 'Login with Google failed. Please try again.');
        }

        $user = User::where('google_id', $socialUser->getId())->first();

        if (! $user) {
            // User does not exist with this google_id, try to find by email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User exists with this email, link their Google account
                $user->update(['google_id' => $socialUser->getId()]);
            } else {
                // No existing user, create one IF the intent was for a patient
                $intendedPanel = session('oauth_intended_panel', 'patient');
                if ($intendedPanel !== 'patient') {
                    // Do not allow on-the-fly registration for staff roles
                    return redirect()->route("filament.{$intendedPanel}.auth.login")
                        ->withErrors(['email' => 'Your account must be created by an administrator or via the registration form. Please log in with your password.']);
                }

                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'google_id' => $socialUser->getId(),
                    'password' => Hash::make(Str::random(24)), // Create a secure random password
                    'is_patient' => true, // Default new signups to patient
                ]);
                $user->patientProfile()->create(['full_name' => $user->name]);
            }
        }

        Auth::login($user);
        session()->forget('oauth_intended_panel');

        // Check if profile is complete (e.g., phone number is missing)
        if (empty($user->phone)) {
            session(['oauth_needs_completion' => true]);

            return redirect()->route('auth.complete-profile');
        }

        $panel = $user->is_pharmacist ? 'pharmacy' : ($user->is_technician ? 'technician' : 'patient');

        return redirect()->intended(route("filament.{$panel}.pages.dashboard"));
    }
}
