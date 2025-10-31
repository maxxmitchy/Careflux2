<?php

namespace App\Filament\Resources\Admin\CounselingJourneys\Pages;

use App\Filament\Resources\Admin\CounselingJourneys\CounselingJourneyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCounselingJourney extends EditRecord
{
    protected static string $resource = CounselingJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
