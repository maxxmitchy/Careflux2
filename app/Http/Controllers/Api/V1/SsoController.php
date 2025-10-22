<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Src\Shared\Domain\Models\User;

class SsoController extends Controller
{
    /**
     * Called by a trusted server (the storefront) to generate an SSO token.
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::find($validated['user_id']);

        // Authorization check: Ensure the authenticated entity (the pharmacy)
        // is allowed to generate a token for this user. (Can be added later)

        $token = Str::random(60);

        // Store the token with a short lifespan (e.g., 30 seconds)
        Cache::put("sso:{$token}", $user->id, now()->addSeconds(30));

        return response()->json(['sso_token' => $token]);
    }
}
