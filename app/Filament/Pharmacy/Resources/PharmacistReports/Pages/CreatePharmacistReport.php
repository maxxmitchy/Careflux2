<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Pages;

use App\Filament\Pharmacy\Resources\PharmacistReports\PharmacistReportResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePharmacistReport extends CreateRecord
{
    protected static string $resource = PharmacistReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
