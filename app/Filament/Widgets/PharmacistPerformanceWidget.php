<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Src\Shared\Domain\Models\User;

class PharmacistPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Pharmacist Performance';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_pharmacist', true)
                    ->withCount(['assignedPatients', 'gamificationLedgerEntries'])
                    ->orderByDesc('points_balance')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('pharmacy.name'),
                Tables\Columns\TextColumn::make('points_balance')->label('Gamification Points')->sortable(),
                Tables\Columns\TextColumn::make('assigned_patients_count')->label('Assigned Patients')->sortable(),
            ]);
    }
}
