<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->helperText('An internal name for this chat scenario, e.g., "Homepage Blood Pressure Demo".'),
                Toggle::make('is_active')
                    ->helperText('Only one conversation can be active at a time for the homepage demo.'),
            ]);
    }
}
