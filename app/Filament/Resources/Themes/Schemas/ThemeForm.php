<?php

namespace App\Filament\Resources\Themes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ThemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Theme Name')
                    ->placeholder('e.g., Modern Green')
                    ->required()
                    ->maxLength(255),

                TextInput::make('key')
                    ->label('Key')
                    ->placeholder('e.g., modern-green')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                FileUpload::make('preview_image_path')
                    ->label('Preview Image')
                    ->image()
                    ->directory('themes/previews')
                    ->visibility('public'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
