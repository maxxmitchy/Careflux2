<?php

namespace App\Filament\Resources\Pharmacies\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Src\Pharmacy\Domain\Models\Pharmacy;

class PharmaciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('users_count')->counts('users')->label('Staff'),
                IconColumn::make('is_approved')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('Approval Status'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('generate_token')
                    ->label('Generate API Token')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Storefront API Token')
                    ->modalDescription('This will invalidate any old API token for this pharmacy and generate a new one. The new token must be copied and saved in the storefront application immediately.')
                    ->action(function (Pharmacy $record) {
                        $token = null; // Initialize the variable

                        // Use a database transaction for safety
                        DB::transaction(function () use ($record, &$token) {
                            // 1. Delete all existing Sanctum tokens for this pharmacy.
                            $record->tokens()->delete();

                            // 2. Create the new plain-text token.
                            $plainTextToken = $record->createToken('storefront-token-'.$record->id)->plainTextToken;
                            // Encrypt the plain-text token and save it to the pharmacy record.
                            $record->update(['api_token' => Crypt::encryptString($plainTextToken)]);

                            // Assign the plain-text token to the outer scope variable for notification.
                            $token = $plainTextToken;
                        });

                        // Display the plain-text token to the admin for them to copy
                        if ($token) {
                            Notification::make()
                                ->title('New API Token Generated (Copy Immediately)')
                                ->body($token)
                                ->persistent()
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
