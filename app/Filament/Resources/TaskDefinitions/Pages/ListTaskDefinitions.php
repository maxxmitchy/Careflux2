<?php

namespace App\Filament\Resources\TaskDefinitions\Pages;

use App\Filament\Resources\TaskDefinitions\TaskDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaskDefinitions extends ListRecords
{
    protected static string $resource = TaskDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
