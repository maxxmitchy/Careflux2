<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeamTask extends EditRecord
{
    protected static string $resource = TeamTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
