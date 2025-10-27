<?php

namespace App\Filament\Resources\EarningShowcases\Pages;

use App\Filament\Resources\EarningShowcases\EarningShowcaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEarningShowcases extends ListRecords
{
    protected static string $resource = EarningShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
