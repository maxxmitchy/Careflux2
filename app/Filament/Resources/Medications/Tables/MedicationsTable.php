<?php

namespace App\Filament\Resources\Medications\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Src\Medication\Domain\Models\Medication;

class MedicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('generic_name')->searchable(),
                IconColumn::make('is_prescription')->boolean(),
                TextColumn::make('variants_count')->counts('variants')->label('Variants'),
                TextColumn::make('creator.name')->label('Submitted By'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Medication $record) {
                        $record->update(['status' => 'approved']);
                        // Optionally, notify the original submitter
                        if ($record->creator) {
                            Notification::make()
                                ->title('Medication Approved')
                                ->body("Your submission for '{$record->name}' has been approved and is now available in the catalog.")
                                ->success()
                                ->sendToDatabase($record->creator);
                        }
                    })
                    ->visible(fn (Medication $record): bool => $record->status === 'pending_review'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
