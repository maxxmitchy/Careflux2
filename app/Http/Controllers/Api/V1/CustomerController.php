<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\Group;

#[Group('Authenticated Customer')]
class CustomerController extends Controller
{
    /**
     * Get Authenticated Patient Profile
     *
     * Retrieves the complete patient profile for the currently authenticated user.
     *
     * @authenticated
     */
    public function getProfile(Request $request)
    {
        return new PatientResource($request->user()->patientProfile);
    }

    /**
     * Get Authenticated Patient Order History
     *
     * Retrieves a paginated list of the authenticated patient's past orders (invoices).
     *
     * @authenticated
     */
    public function getOrders(Request $request)
    {
        // Fetch all invoices for the patient profile, regardless of status.
        $orders = $request->user()->patientProfile->orders()
            ->with(['items', 'pharmacy']) // Eager-load for performance
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $patient = $user->patientProfile;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:10', Rule::unique('users')->ignore($user->id)],
            // Add any other fields from the patient profile you want to be updatable
            'location_area' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        if ($patient) {
            $patient->update([
                'full_name' => $validated['name'],
                'phone' => $validated['phone'],
                'location_area' => $validated['location_area'],
            ]);
        }

        return new PatientResource($patient->fresh());
    }
}
