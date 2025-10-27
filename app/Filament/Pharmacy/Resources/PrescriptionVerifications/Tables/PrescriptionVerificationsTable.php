<?php

namespace App\Filament\Pharmacy\Resources\PrescriptionVerifications\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;

class PrescriptionVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')->searchable(),
                TextColumn::make('medicationVariant.medication.name')->label('Product'),
                TextColumn::make('medicationVariant.name')->label('Variant'),
                TextColumn::make('reference_code')->label('Patient Ref Code')->copyable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning', 'verified' => 'primary', 'completed' => 'success', 'rejected' => 'danger', default => 'gray'
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->schema([
                        TextInput::make('quantity_allowed')->numeric()->required()->minValue(1)->default(1),
                        TextInput::make('final_code')->label('Final Code for Patient')->default(fn () => Str::upper(Str::random(6)))->required(),
                    ])
                    ->action(function (array $data, PrescriptionVerification $record) {
                        $record->update([
                            'status' => 'verified',
                            'quantity_allowed' => $data['quantity_allowed'],
                            'final_verification_code' => Hash::make($data['final_code']),
                            'expires_at' => now()->addHours(1), // Code expires in 1 hour
                        ]);

                        // Send the plain-text code to the patient via SMS/WhatsApp
                        // YourMessagingService::send($record->patient->phone, "Your verification code is {$data['final_code']}");

                        Notification::make()->title('Prescription Approved')->body('The final code has been generated. Please provide it to the patient.')->success()->send();
                    })
                    ->visible(fn (PrescriptionVerification $record) => $record->status === 'pending'),
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
