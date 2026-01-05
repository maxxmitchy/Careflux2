<?php

namespace App\Filament\Resources\TaskDefinitions\Pages;

use App\Filament\Resources\TaskDefinitions\TaskDefinitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaskDefinition extends EditRecord
{
    protected static string $resource = TaskDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
