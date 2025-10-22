<?php

namespace Src\Patient\Application\Actions;

use App\Events\PatientOnboarded;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class OnboardPatientAction
{
    public function execute(array $patientData, int $pharmacistId, int $communityId): Patient
    {
        return DB::transaction(function () use ($patientData, $pharmacistId, $communityId) {
            // Step 1: Find or Create the User account based on phone number.
            // If email is provided, it will be added; otherwise, it can be null.
            $user = User::firstOrCreate(
                ['phone' => $patientData['phone']],
                [
                    'name' => $patientData['name'],
                    'email' => $patientData['email'] ?? null,
                    'password' => Hash::make($patientData['password']), // Secure, random password for new users
                    'is_patient' => true,
                    'verified_at' => now(), // Patient accounts are active immediately
                ]
            );

            // Step 2: Critical check - Does this user already have a patient profile?
            if ($user->wasRecentlyCreated === false && $user->patientProfile()->exists()) {
                // This user is already a patient, possibly managed by another pharmacist.
                // Throwing an exception is the correct action to inform the calling code.
                throw new Exception('A patient profile already exists for this user/phone number and cannot be duplicated.');
            }

            // Step 3: Create the Patient profile and link it to the User and Pharmacist.
            $patient = $user->patientProfile()->create([
                'pharmacist_id' => $pharmacistId,
                'community_id' => $communityId,
                'full_name' => $patientData['name'],
                'phone' => $patientData['phone'],
                'date_of_birth' => $patientData['date_of_birth'] ?? null,
                'gender' => $patientData['gender'] ?? null,
                'location_area' => $patientData['location_area'] ?? null,

                'takes_regular_medications' => $patientData['takes_regular_medications'] ?? false,
                'medication_list' => $patientData['medication_list'] ?? null,
                'known_health_conditions' => $patientData['known_health_conditions'] ?? null,
                'last_health_check' => $patientData['last_health_check'] ?? null,
                'monthly_medicine_spend' => $patientData['monthly_medicine_spend'] ?? null,
                'usual_purchase_location' => $patientData['usual_purchase_location'] ?? null,
                'received_pharmacist_follow_up' => $patientData['received_pharmacist_follow_up'] ?? false,
                'expectations_from_pharmacist' => $patientData['expectations_from_pharmacist'] ?? null,
                'consents_to_contact' => $patientData['consents_to_contact'] ?? false,
            ]);

            // Step 4: Ensure the new patient has a wallet.
            $patient->ensureWalletExists();

            // Future enhancement: Dispatch a "PatientOnboarded" event here
            PatientOnboarded::dispatch($patient);

            return $patient;
        });
    }
}
