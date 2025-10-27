<?php

namespace Src\Pharmacy\Application\Actions;

use App\Events\VerificationInitiated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;
use Src\Shared\Domain\Models\User; // <-- THE FIX: Expect a PharmacyProduct

class InitiateVerificationAction
{
    public function execute(Patient $patient, PharmacyProduct $product): PrescriptionVerification
    {
        $verifier = $product->user;

        if (! $verifier || ! $verifier->is_pharmacist) {
            $verifier = $product->pharmacy->users()
                ->where('is_pharmacist', true)
                ->whereNotNull('verified_at')
                ->first();
        }

        if (! $verifier) {
            Log::critical("Could not find a valid pharmacist verifier for PharmacyProduct ID: {$product->id} at Pharmacy ID: {$product->pharmacy_id}.");
            // For system resilience, we can assign to a default admin (e.g., user ID 1)
            $verifier = User::find(1);
            if (! $verifier) {
                throw new \Exception('No valid verifier or fallback admin could be found for prescription verification.');
            }
        }

        $verification = PrescriptionVerification::create([
            'patient_id' => $patient->id,
            'medication_variant_id' => $product->medication_variant_id, // Get the variant ID from the product
            'verifier_id' => $verifier->id,
            'status' => 'pending',
            'reference_code' => 'CFX-'.strtoupper(Str::random(6)),
            'expires_at' => now()->addMinutes(60),
        ]);

        VerificationInitiated::dispatch($verification);

        return $verification;
    }
}
