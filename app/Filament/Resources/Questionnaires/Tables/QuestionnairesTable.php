<?php

namespace App\Filament\Resources\Questionnaires\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class QuestionnairesTable
{
    public static function configure(Table $table, $isAdmin = true): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                IconColumn::make('is_template')->boolean()->label('Template'),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->visible($isAdmin),
                TextColumn::make('creator.name')->label('Created By'),
                TextColumn::make('questions_count')->counts('questions')->label('Questions'),
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
