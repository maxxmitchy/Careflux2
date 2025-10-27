<?php

namespace Src\Patient\Application\Actions;

use App\Events\PatientOnboarded;
use Exception;
use Illuminate\Support\Facades\DB;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class AssignPatientToPharmacistAction
{
    private const COMMUNITY_CAPACITY_LIMIT = 200;

    public function execute(Patient $patient): bool
    {
        return DB::transaction(function () use ($patient) {
            // Failsafe: Do not re-assign a patient who already has a pharmacist.
            if ($patient->pharmacist_id) {
                return false;
            }

            // --- Step 1: Find all eligible pharmacists ---
            $eligiblePharmacists = User::query()
                ->where('is_pharmacist', true)
                ->whereNotNull('verified_at')
                ->with('pharmacy.city', 'pharmacy.state') // Eager load location data
                ->withCount('assignedPatients') // Get current workload
                ->get();

            if ($eligiblePharmacists->isEmpty()) {
                throw new Exception('No eligible pharmacists found to assign the patient to.');
            }

            // --- Step 2: Score each pharmacist ---
            $scoredPharmacists = [];
            foreach ($eligiblePharmacists as $pharmacist) {
                $score = 0;

                // Find or create the pharmacist's personal community
                $personalCommunity = $pharmacist->communities()->firstOrCreate(
                    ['owner_id' => $pharmacist->id, 'owner_type' => User::class],
                    ['name' => $pharmacist->name."'s Personal Community"]
                );

                // Hard Filter: Community Capacity
                if ($personalCommunity->patients()->count() >= self::COMMUNITY_CAPACITY_LIMIT) {
                    continue; // This pharmacist is not eligible
                }

                // Scoring: Location
                if ($pharmacist->pharmacy && $patient->location_area) {
                    // This is a simplified location match. A production system might use Google Maps API.
                    if (stripos($patient->location_area, $pharmacist->pharmacy->city?->name) !== false) {
                        $score += 50;
                    } elseif (stripos($patient->location_area, $pharmacist->pharmacy->state?->name) !== false) {
                        $score += 20;
                    }
                }

                // Scoring: Workload Penalty
                $score -= ($pharmacist->assigned_patients_count * 0.5);

                $scoredPharmacists[] = [
                    'pharmacist' => $pharmacist,
                    'community' => $personalCommunity,
                    'score' => $score,
                ];
            }

            if (empty($scoredPharmacists)) {
                throw new Exception('No pharmacists with available community capacity were found.');
            }

            // --- Step 3: Select the best pharmacist ---
            usort($scoredPharmacists, fn ($a, $b) => $b['score'] <=> $a['score']);
            $bestMatch = $scoredPharmacists[0];

            // --- Step 4: Perform the assignment ---
            $patient->update([
                'pharmacist_id' => $bestMatch['pharmacist']->id,
                'community_id' => $bestMatch['community']->id,
            ]);

            // Dispatch PatientOnboarded event to send welcome notifications
            PatientOnboarded::dispatch($patient);

            return true;
        });
    }
}
