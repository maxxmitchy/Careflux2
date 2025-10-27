<?php

namespace App\Filament\Pharmacy\Resources\QuoteRequests\Pages;

use App\Filament\Pharmacy\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuoteRequests extends ListRecords
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
