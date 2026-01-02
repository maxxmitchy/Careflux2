<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
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
                    ->relationship('taskDefinition', 'name',
                        fn (Builder $query) => $query->where('is_custom', false)->orWhere('pharmacy_id', $user->pharmacy_id)
                    )
                    ->required()->searchable()->preload()->live()
                    ->createOptionForm([
                        TextInput::make('name')->required()->placeholder('e.g., Conduct weekly stock count'),
                        Select::make('type_preset')
                            ->label('Task Template')
                            ->options([
                                'standard' => 'Standard (Generic)',
                                'discovery' => 'New Product Discovery (Requires 10 items)',
                            ])
                            ->default('standard')
                            ->live(),
                        Textarea::make('description')->rows(2)->placeholder('e.g., A weekly check of all high-value medications.'),
                        TextInput::make('points')->numeric()->required()->default(5),
                    ])
                    ->createOptionUsing(function (array $data) use ($user): int {
                        // Determine the Key based on selection
                        $keyPrefix = match ($data['type_preset'] ?? 'standard') {
                            'discovery' => 'TECHNICIAN_NEW_PRODUCT_DISCOVERY',
                            default => 'CUSTOM',
                        };

                        // Ensure uniqueness if it's custom
                        $key = $keyPrefix === 'CUSTOM'
                            ? 'CUSTOM-'.$user->pharmacy_id.'-'.Str::upper(Str::random(6))
                            : $keyPrefix.'-'.$user->pharmacy_id;

                        $newTaskDef = TaskDefinition::create([
                            'key' => $key,
                            'name' => $data['name'],
                            'description' => $data['description'],
                            'points' => $data['points'],
                            'is_custom' => true,
                            'pharmacy_id' => $user->pharmacy_id,
                        ]);

                        return $newTaskDef->id;
                    }),

                Select::make('product_ids')
                    ->label('Product(s) to Check')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    // 1. Manually define the options query.
                    ->options(
                        PharmacyProduct::where('pharmacy_id', $user->pharmacy_id)
                            ->with('medicationVariant.medication')
                            ->get()
                            ->pluck('name', 'id')
                    )
                    // This is only necessary if the options are not preloaded and you need search
                    ->getSearchResultsUsing(function (string $search) use ($user) {
                        return PharmacyProduct::query()
                            ->where('pharmacy_id', $user->pharmacy_id)
                            ->whereHas('medicationVariant.medication', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                            ->limit(50)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->visible(function (Get $get): bool {
                        $taskDefId = $get('task_definition_id');
                        if (! $taskDefId) {
                            return false;
                        }
                        $taskDef = TaskDefinition::find($taskDefId);

                        return in_array($taskDef?->key, ['TECHNICIAN_PRICE_VERIFY', 'TECHNICIAN_EXPIRY_LOG']);
                    }),

                Select::make('assigned_to_user_id')
                    ->label('Assign To')
                    ->options(User::where('pharmacy_id', $user->pharmacy_id)->pluck('name', 'id'))
                    ->required(),

                DatePicker::make('due_at'),
            ]);
    }
}
