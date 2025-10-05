<?php

namespace App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages;

use App\Filament\Pharmacy\Resources\PrescriptionVerifications\PrescriptionVerificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrescriptionVerification extends ViewRecord
{
    protected static string $resource = PrescriptionVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
