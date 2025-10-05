<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Shared\Domain\Models\User;

class TeamTaskForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Select::make('task_definition_id')
                    ->label('Task Type')
                    ->relationship(
                        'taskDefinition',
                        'name',
                        // The dropdown shows global tasks OR custom tasks from this manager's pharmacy
                        fn (Builder $query) => $query->where('is_custom', false)->orWhere('pharmacy_id', $user->pharmacy_id)
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->placeholder('e.g., Conduct weekly stock count'),
                        Textarea::make('description')->rows(2)->placeholder('e.g., A weekly check of all high-value medications.'),
                        TextInput::make('points')->numeric()->required()->default(5),
                    ])
                    ->createOptionUsing(function (array $data) use ($user): int {
                        // This creates a new TaskDefinition linked to the manager's pharmacy
                        $newTaskDef = TaskDefinition::create([
                            'key' => 'CUSTOM-'.$user->pharmacy_id.'-'.Str::upper(Str::random(6)),
                            'name' => $data['name'],
                            'description' => $data['description'],
                            'points' => $data['points'],
                            'is_custom' => true,
                            'pharmacy_id' => $user->pharmacy_id,
                        ]);

                        return $newTaskDef->id;
                    }),

                Select::make('assigned_to_user_id')
                    ->label('Assign To')
                    ->options(User::where('pharmacy_id', $user->pharmacy_id)->pluck('name', 'id'))
                    ->required(),

                DatePicker::make('due_at'),
            ]);
    }
}
