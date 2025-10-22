<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Src\Patient\Application\Actions\OnboardPatientAction;
use Src\Shared\Domain\Models\User;

#[Group('Customer Authentication')]
class CustomerAuthController extends Controller
{
    /**
     * Register a new Patient account.
     *
     * Creates a new User and linked Patient profile. On success, it returns the user object
     * and a Sanctum API token for authenticating subsequent requests.
     */
    public function register(Request $request, OnboardPatientAction $onboardAction): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'min:10', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // This action correctly creates both User and Patient models.
        $patient = $onboardAction->execute($validated, 1, 1); // Assign to default pharmacist/community
        $user = $patient->user;

        $token = $user->createToken('storefront_token')->plainTextToken;

        return response()->json([
            'user' => new AuthUserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Log in a Patient.
     *
     * Authenticates a user with their email and password. On success, it returns the user object
     * and a new Sanctum API token.
     */
    public function login(Request $request): JsonResponse
    {
        Log::info('CustomerAuthController@login method was reached.');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        Log::info([$request->email, $request->password, $user->password]);

        if (! $user || ! Hash::check($request->password, $user->password) || ! $user->is_patient) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('storefront_token')->plainTextToken;

        return response()->json([
            'user' => new AuthUserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Log out a Patient.
     *
     * Revokes the currently used Sanctum API token.
     *
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}
