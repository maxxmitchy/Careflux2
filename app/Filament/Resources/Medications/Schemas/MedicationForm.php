<?php

namespace App\Filament\Resources\Medications\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MedicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->image()->disk('public')->directory('medications')->columnSpanFull()->imageEditor(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('generic_name')->maxLength(255),
                Textarea::make('description')->columnSpanFull(),
                Toggle::make('is_prescription')->label('Requires Prescription')->default(false),
            ]);
    }
}
