<?php

namespace App\Filament\Technician\Resources\Tasks\Pages;

use App\Filament\Technician\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        $user = Filament::auth()->user();

        if ($user->is_manager) {
            return [
                CreateAction::make(),
            ];
        }

        return [];
    }
}
