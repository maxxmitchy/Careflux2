<?php

namespace App\Filament\Patient\Resources\MyQuoteRequests\Pages;

use App\Filament\Patient\Resources\MyQuoteRequests\MyQuoteRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMyQuoteRequests extends ManageRecords
{
    protected static string $resource = MyQuoteRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
