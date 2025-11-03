<?php

namespace App\Filament\Pharmacy\Resources\PharmacyProducts\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class PharmacyProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label(''),
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('medicationVariant.medication', function (Builder $q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere('generic_name', 'like', "%{$search}%");
                            });
                        }
                    ),
                TextColumn::make('price')->money('NGN', 100),
                TextColumn::make('stock')->numeric(),
            ])
            ->filters([
                //
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
