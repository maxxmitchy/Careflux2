<?php

namespace App\Filament\Pharmacy\Resources\Tasks\Pages;

use App\Filament\Pharmacy\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'completed' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
            'all' => Tab::make('All Tasks'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
