<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\StorefrontSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Pharmacy\Domain\Models\Pharmacy; // Import the Pharmacy model

class StorefrontSettingsController extends Controller
{
    /**
     * Handle the incoming request for storefront settings.
     * This endpoint expects to be authenticated via a token belonging to a Pharmacy model.
     */
    public function __invoke(Request $request): JsonResponse|StorefrontSettingsResource
    {
        // Log::info('StorefrontSettingsController invoked. ' . $request);
        // 1. Get the authenticated entity. In this server-to-server context,
        //    the authenticated "user" is the Pharmacy model itself.
        /** @var \Src\Pharmacy\Domain\Models\Pharmacy|null $pharmacy */
        $pharmacy = $request->user();

        // 2. Authorization: Check if the authenticated entity is indeed a Pharmacy.
        if (! $pharmacy instanceof Pharmacy) {
            // This would happen if a user token was accidentally used.
            return response()->json([
                'message' => 'Forbidden: Invalid token type. A pharmacy-level API token is required.',
            ], 403);
        }

        // (Optional) Further check if the pharmacy is approved.
        if (! $pharmacy->is_approved) {
            return response()->json([
                'message' => 'Forbidden: This pharmacy storefront is not currently active.',
            ], 403);
        }

        // 3. Fetch the settings from the authenticated pharmacy.
        $settings = $pharmacy->theme_settings ?? [];

        // 4. Pass the raw settings array to the resource for transformation.
        return new StorefrontSettingsResource($settings);
        // --- END OF FIX ---
    }
}
