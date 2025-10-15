<?php

namespace App\Filament\Resources\DeliveryRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliveryRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pharmacy_id')
                    ->relationship('pharmacy', 'name')
                    ->label('Origin Pharmacy')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('delivery_zone_id')
                    ->relationship('deliveryZone', 'name')
                    ->label('Destination Zone')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cost_kobo')
                    ->label('Cost (in Kobo)')
                    ->numeric()
                    ->required()
                    ->helperText('Enter the amount in the smallest currency unit. e.g., ₦1,500 should be entered as 150000.'),
            ]);
    }
}
