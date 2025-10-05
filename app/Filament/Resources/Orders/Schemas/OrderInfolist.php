<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Order Details')
                        ->schema([
                            TextEntry::make('invoice_number'),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('created_at')->dateTime(),
                            TextEntry::make('pharmacy.name'),
                        ])->columnSpan(1),

                    Section::make('Patient Details')
                        ->schema([
                            TextEntry::make('patient.full_name'),
                            TextEntry::make('patient.phone'),
                            TextEntry::make('patient.email'),
                        ])->columnSpan(1),

                    Section::make('Shipping Details')
                        ->schema([
                            TextEntry::make('shipping_name'),
                            TextEntry::make('shipping_phone'),
                            TextEntry::make('shipping_location_area'),
                        ])->columnSpan(1),
                ]),

                Section::make('Financials')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('description'),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('price')->money('NGN'),
                                TextEntry::make('total')->money('NGN'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('subtotal')->money('NGN')->columnSpan(1),
                                TextEntry::make('delivery_fee')->money('NGN')->columnSpan(1),
                                TextEntry::make('total')->money('NGN')->weight('bold')->columnSpan(2)->alignEnd(),
                            ])->columnSpan('full'),
                    ]),

                Section::make('Order History')
                    ->schema([
                        RepeatableEntry::make('events')
                            ->schema([
                                TextEntry::make('created_at')->dateTime()->label('Timestamp'),
                                TextEntry::make('event_type')->badge()->label('Event'),
                                TextEntry::make('user.name')->label('Triggered By'),
                                TextEntry::make('metadata.reason')->label('Reason/Notes'),
                            ])->columns(4),
                    ]),
            ]);
    }
}
