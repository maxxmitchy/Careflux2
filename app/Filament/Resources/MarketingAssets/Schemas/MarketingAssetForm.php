<?php

namespace App\Filament\Resources\MarketingAssets\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketingAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review Asset')
                    ->description('Review the content created by the pharmacist. The main control here is to activate it.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active & Publicly Visible')
                            ->helperText('Activating this will make the wishlist or care package appear on the public website.'),
                    ]),

                Section::make('Submitted Content (Read-Only)')
                    ->schema([
                        TextInput::make('title')->disabled(),
                        TextInput::make('user.name')->label('Creator')->disabled(),
                        TextInput::make('pharmacy.name')->label('Pharmacy')->disabled(),
                        Repeater::make('product_data')
                            ->schema([
                                TextInput::make('name')->disabled(),
                                TextInput::make('price')->disabled()->prefix('₦'),
                            ])
                            ->disabled()
                            ->columns(2),
                    ])->collapsed(),
            ]);
    }
}
