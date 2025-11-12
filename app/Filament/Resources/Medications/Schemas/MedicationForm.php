<?php

namespace App\Filament\Resources\Medications\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MedicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->image()->disk('public')->directory('medications')->visibility('public')->columnSpanFull()->imageEditor(),
                TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('generic_name')->maxLength(255),
                Textarea::make('description')->columnSpanFull(),
                Toggle::make('is_prescription')->label('Requires Prescription')->default(false),

                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name') // Use the relationship we just defined
                    ->multiple() // Allow selecting multiple categories
                    ->preload() // Eager-load options for a better UX
                    ->searchable()
                    ->createOptionForm([ // Allow creating new categories on the fly
                        TextInput::make('name')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')->required()->unique(),
                        TextInput::make('category_code')
                            ->label('Category Code')
                            ->required()
                            ->maxLength(10),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
