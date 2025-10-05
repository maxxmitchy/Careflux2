<?php

namespace App\Filament\Technician\Resources\Tasks\Pages;

use App\Filament\Technician\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id(); // The manager is the creator

        return $data;
    }
}
