<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Shared\Domain\Models\User;
use Src\Subscription\Application\Actions\StartTrialSubscriptionAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('pharmacy.name')->searchable(),
                IconColumn::make('is_pharmacist')->boolean(),
                IconColumn::make('verified_at')->boolean()->label('Verified'),
            ])
            ->filters([
                Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('verified_at')->where('is_pharmacist', true))
                    ->label('Unverified Pharmacists')
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve User & Pharmacy')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record, StartTrialSubscriptionAction $startTrialAction) {
                        if ($record->pharmacy) {
                            $record->pharmacy->update(['is_approved' => true]);
                            // This is where we call our action to start the trial
                            $startTrialAction->execute($record->pharmacy);
                        }
                        $record->update(['verified_at' => now()]);

                        Notification::make()
                            ->title('User and Pharmacy Approved')
                            ->success()
                            ->send();
                    })
                    // Only show this button for unverified pharmacists who belong to an unapproved pharmacy
                    ->visible(fn (User $record): bool => $record->is_pharmacist && is_null($record->verified_at) && $record->pharmacy && ! $record->pharmacy->is_approved
                    ),

                Action::make('approve_user_only')
                    ->label('Approve User')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Approve New Team Member')
                    ->modalDescription('This will verify the user and grant them access to their approved pharmacy.')
                    ->action(function (User $record) {
                        $record->update(['verified_at' => now()]);
                        Notification::make()->title('User has been approved.')->success()->send();
                    })
                    ->visible(fn (User $record): bool => ($record->is_pharmacist || $record->is_technician) &&
                        is_null($record->verified_at) &&
                        $record->pharmacy &&
                        $record->pharmacy->is_approved
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
