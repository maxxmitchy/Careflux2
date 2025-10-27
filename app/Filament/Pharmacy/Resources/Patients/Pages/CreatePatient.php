<?php

namespace App\Filament\Pharmacy\Resources\Patients\Pages;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Src\Patient\Application\Actions\OnboardPatientAction;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Instantiate our action from the service container
            $action = app(OnboardPatientAction::class);

            // Execute the action with all the necessary data
            $patient = $action->execute(
                patientData: $data,
                pharmacistId: auth()->id(),
                communityId: $data['community_id']
            );

            Notification::make()
                ->title('Patient Onboarded Successfully')
                ->success()
                ->send();

            return $patient;

        } catch (\Exception $e) {
            // If our action throws an exception (e.g., duplicate user),
            // we catch it, show a user-friendly notification, and halt the process.
            Notification::make()
                ->title('Error Onboarding Patient')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // This stops Filament from trying to redirect and causing a 404.
            $this->halt();
        }
    }
}
