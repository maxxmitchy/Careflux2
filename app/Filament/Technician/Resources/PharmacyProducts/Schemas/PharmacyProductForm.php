<?php

namespace App\Filament\Technician\Resources\PharmacyProducts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PharmacyProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stock')
                    ->label('Current Stock Quantity')
                    ->numeric()->integer()->required(),
                TextInput::make('price')
                    ->label('Price (in Kobo)')
                    ->numeric()->integer()->required()
                    ->helperText('Enter the price in the smallest unit. e.g., ₦1,500.50 should be 150050.'),
                TextInput::make('nafdac_number'),
            ]);
    }
}
