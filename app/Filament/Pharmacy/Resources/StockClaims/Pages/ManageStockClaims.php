<?php

namespace App\Filament\Pharmacy\Resources\StockClaims\Pages;

use App\Filament\Pharmacy\Resources\StockClaims\StockClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStockClaims extends ManageRecords
{
    protected static string $resource = StockClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
