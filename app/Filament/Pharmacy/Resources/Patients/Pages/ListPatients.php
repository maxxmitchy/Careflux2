<?php

namespace App\Filament\Pharmacy\Resources\Patients\Pages;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Src\Patient\Domain\Models\Patient;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'my_patients' => Tab::make('My Patients')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pharmacist_id', auth()->id())),
            'unassigned' => Tab::make('Unassigned in My Community')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('pharmacist_id'))
                ->badge(
                    Patient::query()
                        ->whereNull('pharmacist_id')
                        ->whereIn('community_id', Auth::user()->communities()->pluck('id'))
                        ->count()
                )
                ->badgeColor('warning'),
        ];
    }
}
