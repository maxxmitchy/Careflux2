<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Pages;

use App\Filament\Pharmacy\Resources\PharmacistReports\PharmacistReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacistReports extends ListRecords
{
    protected static string $resource = PharmacistReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
