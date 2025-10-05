<?php

namespace App\Filament\Technician\Resources\Tasks\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Shared\Domain\Models\User;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentUser = Filament::auth()->user();

        return $schema
            ->components([
                Select::make('task_definition_id')
                    ->label('Task Type')
                    ->relationship('taskDefinition', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Task Name')
                            ->required()
                            ->placeholder('e.g., Conduct weekly stock count')
                            ->helperText('A short, clear name for the new task.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->placeholder('e.g., A weekly check of all high-value medications in the cold storage.')
                            ->helperText('(Optional) Provide more context for what this task involves.'),

                        TextInput::make('points')
                            ->label('Points Awarded')
                            ->numeric()
                            ->required()
                            ->default(5)
                            ->helperText('The number of gamification points awarded upon completion.'),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $newTask = TaskDefinition::create([
                            // Generate a unique key for the new custom task
                            'key' => 'CUSTOM-'.Str::upper(Str::slug($data['name'])).'-'.Str::random(4),
                            'name' => $data['name'],
                            'description' => $data['description'],
                            'points' => $data['points'],
                            'is_active' => true, // Custom tasks are active by default
                        ]);

                        return $newTask->id;
                    }),

                Select::make('assigned_to_user_id')
                    ->label('Assign To')
                    ->options(
                        // A manager can only assign tasks to users in their own pharmacy.
                        User::where('pharmacy_id', $currentUser->pharmacy_id)->pluck('name', 'id')
                    )
                    ->required(),

                DateTimePicker::make('due_at')->columns(1),
            ]);
    }
}
