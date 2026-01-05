<?php

namespace App\Filament\Resources\TaskDefinitions\Pages;

use App\Filament\Resources\TaskDefinitions\TaskDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskDefinition extends CreateRecord
{
    protected static string $resource = TaskDefinitionResource::class;
}
