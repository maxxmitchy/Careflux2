<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Pages;

use App\Filament\Pharmacy\Resources\PharmacistReports\PharmacistReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacistReport extends ViewRecord
{
    protected static string $resource = PharmacistReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
