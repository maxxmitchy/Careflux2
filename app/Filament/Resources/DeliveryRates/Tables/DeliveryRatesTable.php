<?php

namespace App\Filament\Resources\DeliveryRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveryRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pharmacy.name')->label('From Pharmacy')->searchable()->sortable(),
                TextColumn::make('deliveryZone.name')->label('To Zone')->searchable()->sortable(),
                TextColumn::make('cost_kobo')->money('NGN', 100)->label('Cost')->sortable(),
            ])
            ->filters([
                SelectFilter::make('pharmacy_id')->relationship('pharmacy', 'name')->label('Filter by Pharmacy'),
                SelectFilter::make('delivery_zone_id')->relationship('deliveryZone', 'name')->label('Filter by Zone'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
