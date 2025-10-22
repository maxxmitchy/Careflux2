<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PharmacyResource;
use Knuckles\Scribe\Attributes\Group;
use Src\Pharmacy\Domain\Models\Pharmacy;

#[Group('Pharmacy Endpoints')]
class PharmacyController extends Controller
{
    /**
     * Get Public Pharmacy Details
     *
     * Retrieve the public-facing details for a single approved pharmacy.
     * This endpoint is used by storefronts to display their "About Us" or contact information.
     *
     * @param  Pharmacy  $pharmacy  The ID of the pharmacy to retrieve.
     */
    public function show(Pharmacy $pharmacy)
    {
        // Only show approved pharmacies via the public API
        if (! $pharmacy->is_approved) {
            abort(404);
        }

        return new PharmacyResource($pharmacy->load(['city', 'state', 'users' => fn ($query) => $query->whereNotNull('verified_at')->where('is_admin', false)]));
    }
}
