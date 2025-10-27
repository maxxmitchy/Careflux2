<?php

namespace App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages;

use App\Filament\Pharmacy\Resources\PrescriptionVerifications\PrescriptionVerificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPrescriptionVerification extends EditRecord
{
    protected static string $resource = PrescriptionVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
