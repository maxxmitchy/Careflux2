<?php

namespace App\Filament\Resources\TaskDefinitions\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Src\Gamification\Domain\Models\TaskDefinition;

class TaskDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            // Automatically generate the key from the name for convenience
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('key', Str::of($state)->upper()->snake()->__toString())),

                        TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->helperText('A unique, uppercase, snake_case key for this task (e.g., PATIENT_FOLLOW_UP). This is used by developers and cannot be changed after creation.')
                            ->unique(TaskDefinition::class, 'key', ignoreRecord: true)
                            ->disabledOn('edit'), // Prevent changing the key after creation to avoid breaking the system

                        TextInput::make('points')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('The number of points awarded for completing this task.'),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        
                        Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive tasks cannot be assigned or completed.'),
                    ])->columns(2),
            ]);
    }
}
