<?php

namespace App\Filament\Pharmacy\Resources\Communities\Pages;

use App\Filament\Pharmacy\Resources\Communities\CommunityResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCommunity extends CreateRecord
{
    protected static string $resource = CommunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();
        $data['pharmacy_id'] = $user->pharmacy_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = Filament::auth()->user();
        $community = $this->getRecord();

        // Get the team members selected in the form
        $teamMembers = $this->data['users'] ?? [];

        // Always add the creator to the team
        $teamMembers[] = $user->id;

        // Sync the final, unique list of team members to the community
        $community->users()->sync(array_unique($teamMembers));
    }
}
