<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')->tel(),
                Select::make('pharmacy_id')->relationship('pharmacy', 'name'),
                Toggle::make('is_admin'),
                Toggle::make('is_pharmacist'),
                Toggle::make('is_technician'),
                Toggle::make('is_assistant')->helperText('Assistants can help with inventory management and order processing but have limited access to user and billing features.'),

                Toggle::make('is_manager')->helperText('Managers can view and manage orders, inventory, and users but cannot access billing or marketing features.'),
                Toggle::make('is_patient'),
            ]);
    }
}
