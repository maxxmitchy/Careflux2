<?php

namespace App\Filament\Resources\Admin\CounselingJourneys\Pages;

use App\Filament\Resources\Admin\CounselingJourneys\CounselingJourneyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCounselingJourneys extends ListRecords
{
    protected static string $resource = CounselingJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
