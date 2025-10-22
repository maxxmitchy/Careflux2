<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PharmacyProductResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder; // A powerful package for API filtering
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class PharmacyProductController extends Controller
{
    public function index(Request $request)
    {
        $products = QueryBuilder::for(PharmacyProduct::class)
            ->allowedFilters(['pharmacy_id', 'medication_variant_id'])
            ->with(['pharmacy', 'medicationVariant.medication.categories'])
            ->paginate(1000)
            ->appends($request->query());

        return PharmacyProductResource::collection($products);
    }

    public function show(PharmacyProduct $pharmacyProduct)
    {
        return new PharmacyProductResource($pharmacyProduct->load(['pharmacy', 'medicationVariant.medication']));
    }
}
