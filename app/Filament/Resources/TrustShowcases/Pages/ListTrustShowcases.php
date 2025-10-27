<?php

namespace App\Filament\Resources\TrustShowcases\Pages;

use App\Filament\Resources\TrustShowcases\TrustShowcaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrustShowcases extends ListRecords
{
    protected static string $resource = TrustShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
