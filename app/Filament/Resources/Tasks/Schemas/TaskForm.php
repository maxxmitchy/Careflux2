<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('task_definition_id')
                            ->label('Task Type')
                            ->relationship('taskDefinition', 'name')
                            ->required(),

                        Select::make('assigned_to_user_id')
                            ->label('Assign To User')
                            ->relationship('assignedTo', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('is_pharmacist', true)->orWhere('is_technician', true))
                            ->searchable()
                            ->required(),

                        MorphToSelect::make('subjectable')
                            ->label('Subject of Task (Optional)')
                            ->types([
                                Type::make(\Src\Patient\Domain\Models\Patient::class)->titleAttribute('full_name'),
                                Type::make(\Src\Pharmacy\Domain\Models\PharmacyProduct::class)->titleAttribute('name'),
                            ])
                            ->searchable(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'overdue' => 'Overdue',
                            ])
                            ->required()
                            ->default('pending'),

                        DateTimePicker::make('due_at'),
                    ])->columns(2),
            ]);
    }
}
