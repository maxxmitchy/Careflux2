<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamTasks extends ListRecords
{
    protected static string $resource = TeamTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
