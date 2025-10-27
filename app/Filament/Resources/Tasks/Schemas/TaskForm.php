<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

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
                            ->required()->reactive(),

                        Select::make('assigned_to_user_id')
                            ->label('Assign To User')
                            ->relationship('assignedTo', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('is_pharmacist', true)->orWhere('is_technician', true))
                            ->searchable()
                            ->required(),

                        Select::make('product_ids') // Use a simple array key
                            ->label('Products to Check')
                            ->multiple()
                            ->options(PharmacyProduct::query()->with('medicationVariant.medication')->get()->pluck('name', 'id'))
                            ->preload()->searchable()->required()
                            ->visible(function (Get $get): bool {
                                $taskDef = TaskDefinition::find($get('task_definition_id'));

                                return $taskDef?->key === 'TECHNICIAN_PRICE_VERIFY' || $taskDef?->key === 'TECHNICIAN_EXPIRY_LOG';
                            }),

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
