<?php

namespace App\Filament\Resources\TrustShowcases\Pages;

use App\Filament\Resources\TrustShowcases\TrustShowcaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrustShowcase extends EditRecord
{
    protected static string $resource = TrustShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
