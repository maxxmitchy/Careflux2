<?php

namespace App\Filament\Pharmacy\Resources\Communities\Pages;

use App\Filament\Pharmacy\Resources\Communities\CommunityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommunity extends EditRecord
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
