<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPharmacyVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        // Guard Clause 1: If the user is a guest, do nothing.
        if (! $user = $request->user()) {
            return $next($request);
        }

        // --- PRODUCTION-READY FIX: ADDED ADMIN GUARD ---
        // Guard Clause 2: If the user is an admin, this middleware should not apply to them at all.
        // Let them pass through to whatever they are trying to do (e.g., log out).
        if ($user->is_admin) {
            return $next($request);
        }
        // --- END OF FIX ---

        // If we get here, we know the user is a logged-in pharmacist or technician.

        $pharmacy = $user->pharmacy;
        $currentRouteName = $request->route()->getName();

        // These are routes a pending user is ALWAYS allowed to access.
        $alwaysAllowedRoutes = [
            'filament.pharmacy.pages.pending-approval', // The page they are redirected to
            'filament.pharmacy.auth.logout',            // The ability to log out
            // Add any other pages here, like a profile or support page, if needed.
        ];

        // A user is considered fully approved if their own account is verified AND their pharmacy is approved.
        $isFullyApproved = ! is_null($user->verified_at) && ($pharmacy?->is_approved ?? false);

        // If they are fully approved...
        if ($isFullyApproved) {
            // ...and they somehow land on the pending page, redirect them to the dashboard.
            if ($currentRouteName === 'filament.pharmacy.pages.pending-approval') {
                return redirect()->route('filament.pharmacy.pages.dashboard');
            }

            // ...otherwise, let them proceed to their intended destination.
            return $next($request);
        }

        // If they are NOT fully approved...
        // ...but are trying to access one of the few allowed pages, let them pass.
        if (in_array($currentRouteName, $alwaysAllowedRoutes, true)) {
            return $next($request);
        }

        // --- PRODUCTION-READY FIX: CORRECTED ROUTE NAME ---
        // ...and they are trying to access any other protected page, force them to the pending page.
        return redirect()->route('filament.pharmacy.pages.pending-approval');
        // --- END OF FIX ---
    }
}
