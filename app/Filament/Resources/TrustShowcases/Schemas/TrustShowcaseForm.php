<?php

namespace App\Filament\Resources\TrustShowcases\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TrustShowcaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->helperText('Internal name, e.g., "Homepage Trust Section"'),
                TextInput::make('subheadline')->required(),
                TextInput::make('headline')->required(),
                Textarea::make('description')->required()->rows(3),
                Toggle::make('is_active')->helperText('Activating this will deactivate all others.'),
            ]);
    }
}
