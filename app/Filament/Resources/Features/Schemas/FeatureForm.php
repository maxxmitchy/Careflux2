<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('icon')->helperText('Enter the name of a Heroicon (e.g., "chat-bubble-left-right")'),
                TextInput::make('title')->required(),
                Textarea::make('description')->required(),
                Toggle::make('is_active'),
                TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }
}
