<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTeamTask extends CreateRecord
{
    protected static string $resource = TeamTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The creator is the logged-in manager
        $data['created_by_user_id'] = Auth::id();

        return $data;
    }
}
