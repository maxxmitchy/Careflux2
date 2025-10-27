<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Src\Pharmacy\Domain\Models\Pharmacy;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                FileUpload::make('logo')->image()->disk('public')->directory('partners')->required(),
                Select::make('pharmacy_id')
                    ->label('Associated Pharmacy')
                    ->relationship('pharmacy', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Link this partner to a pharmacy record. If it doesn\'t exist, you can create it here.')

                    // This defines the modal form for creating a new pharmacy
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('address')->required(),
                        TextInput::make('phone')->tel()->required(),
                        // New pharmacies created by an admin are approved by default
                        // We don't need a toggle here.
                    ])

                    // This contains the logic to save the new pharmacy
                    ->createOptionUsing(function (array $data): int {
                        $newPharmacy = Pharmacy::create([
                            'name' => $data['name'],
                            'address' => $data['address'],
                            'phone' => $data['phone'],
                            'is_approved' => true, // Admin-created pharmacies are auto-approved
                        ]);

                        Notification::make()
                            ->title('New pharmacy created successfully')
                            ->success()
                            ->send();

                        // Return the ID of the newly created record
                        return $newPharmacy->id;
                    }),
                Toggle::make('is_active')->label('Active on Homepage'),
                TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }
}
