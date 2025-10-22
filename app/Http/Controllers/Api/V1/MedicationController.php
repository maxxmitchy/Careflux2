<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MedicationResource; // We will create this
use Illuminate\Http\Request;
use Src\Medication\Domain\Models\Medication;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Allow searching the catalog via a query parameter, e.g., /api/v1/medications?search=panadol
        $query = Medication::query()
            ->where('status', 'approved')
            ->when($request->input('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });

        return MedicationResource::collection($query->paginate(100));
    }

    /**
     * Display the specified resource.
     */
    public function show(Medication $medication)
    {
        if ($medication->status !== 'approved') {
            abort(404); // Do not show pending medications via the public API
        }

        return new MedicationResource($medication->load('variants'));
    }
}
