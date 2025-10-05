<?php

namespace App\Filament\Pharmacy\Resources\QuoteRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Select the user (optional)
                Select::make('user_id')
                    ->label('Assigned User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable(),

                // Select the patient (optional)
                Select::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->nullable(),

                // Denormalized patient name
                TextInput::make('patient_name')
                    ->label('Patient Name')
                    ->required()
                    ->maxLength(255),

                // Denormalized patient phone
                TextInput::make('patient_phone')
                    ->label('Patient Phone')
                    ->required()
                    ->maxLength(20),

                // Optional note or message
                Textarea::make('note')
                    ->label('Note / Request Details')
                    ->rows(3)
                    ->placeholder('Optional message or note for the pharmacy'),

                // Status
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'available' => 'Available',
                        'completed' => 'Completed',
                        'unavailable' => 'Unavailable',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
