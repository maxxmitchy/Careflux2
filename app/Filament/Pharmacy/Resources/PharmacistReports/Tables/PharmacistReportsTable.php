<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PharmacistReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('week_ending_date')
                    ->date('F j, Y')
                    ->sortable(),
                TextColumn::make('biggest_win')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Submitted On')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('week_ending_date', 'desc');
    }
}
