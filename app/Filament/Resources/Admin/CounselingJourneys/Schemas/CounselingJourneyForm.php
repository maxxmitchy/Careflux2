<?php

namespace App\Filament\Resources\Admin\CounselingJourneys\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CounselingJourneyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medication_id')->relationship('medication', 'name')->required()->searchable(),
                TextInput::make('name')->required(),
                Textarea::make('description')->columnSpanFull(),
                Toggle::make('is_active'),
            ]);
    }
}
