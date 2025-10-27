<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Src\Patient\Application\Actions\OnboardPatientAction;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    /**
     * Override the default creation handler to use our robust action.
     */
    protected function handleCreate(array $data)
    {
        try {
            $action = app(OnboardPatientAction::class);

            return $action->execute(
                patientData: $data,
                pharmacistId: $data['pharmacist_id'],
                communityId: $data['community_id']
            );

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Onboarding Patient')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
