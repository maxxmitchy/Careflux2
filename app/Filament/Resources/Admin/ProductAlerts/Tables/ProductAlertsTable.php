<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Src\Pharmacovigilance\Domain\Models\ProductAlert;

class ProductAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medication.name'),
                TextColumn::make('title'),
                TextColumn::make('severity')->badge()->color(fn (string $state) => match ($state) {
                    'critical' => 'danger', 'high' => 'warning', default => 'info'
                }),
                TextColumn::make('dispatched_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('dispatch')
                    ->label('Dispatch Alert')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ProductAlert $record) {
                        // DispatchAlertTasksJob::dispatch($record);
                        $record->update(['dispatched_at' => now()]);
                    })
                    ->visible(fn (ProductAlert $record) => is_null($record->dispatched_at)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
