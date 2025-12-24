<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Src\Shared\Domain\Models\User;

class PharmacistValueWidget extends TableWidget
{
    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pharmacist & Pharmacy Value Analysis';

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(
    //             User::query()
    //                 ->where('is_pharmacist', true)
    //                 ->select([
    //                     'users.id',
    //                     'users.name',
    //                     'users.pharmacy_id',
    //                     DB::raw('COUNT(DISTINCT prescriptions.patient_id) as recurring_patients'),
    //                     DB::raw('SUM(invoice_items.total) as total_revenue_kobo'),
    //                 ])
    //                 ->join('patients', 'users.id', '=', 'patients.pharmacist_id')
    //                 ->join('prescriptions', 'patients.id', '=', 'prescriptions.patient_id')
    //                 ->join('invoices', 'patients.id', '=', 'invoices.patient_id')
    //                 ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
    //                 ->where('prescriptions.is_recurring', true)
    //                 ->where('invoices.status', 'paid')
    //                 ->groupBy('users.id', 'users.name', 'users.pharmacy_id')
    //         )
    //         ->columns([
    //             TextColumn::make('name')->label('Pharmacist'),
    //             TextColumn::make('pharmacy.name')->label('Pharmacy'),
    //             TextColumn::make('recurring_patients')->label('Recurring Patients')->numeric()->sortable(),
    //             TextColumn::make('total_revenue_kobo')->label('Total Revenue')->money('NGN')->sortable(),
    //             TextColumn::make('estimated_profit')
    //                 ->label('Est. Gross Profit')
    //                 ->money('NGN')
    //                 ->state(function (User $record): float {
    //                     $markup = $record->pharmacy?->average_markup_percentage;
    //                     if (!$markup) {
    //                         return 0;
    //                     }
    //                     // Formula: Profit = Revenue / (1 + Markup) * Markup
    //                     $costOfGoods = $record->total_revenue_kobo / (1 + ($markup / 100));
    //                     return $record->total_revenue_kobo - $costOfGoods;
    //                 }),
    //         ])
    //         ->filters([
    //             SelectFilter::make('pharmacy')->relationship('pharmacy', 'name'),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_pharmacist', true)
                    // We eager-load only what we need.
                    // The aggregation now happens in PHP using the accessor.
                    ->with([
                        'pharmacy',
                        'assignedPatients:id,pharmacist_id,monthly_medicine_spend',
                    ])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Pharmacist'),

                TextColumn::make('pharmacy.name')
                    ->label('Pharmacy'),

                /**
                 * ESTIMATED POTENTIAL MONTHLY VALUE
                 * Uses Patient::numeric_monthly_spend accessor
                 */
                TextColumn::make('potential_monthly_value')
                    ->label('Est. Potential Monthly Value')
                    ->money('NGN')
                    ->state(function (User $record): int {
                        return $record->assignedPatients
                            ->sum('numeric_monthly_spend') / 100;
                    })
                    ->sortable(),

                /**
                 * REALIZED REVENUE (ALL TIME)
                 */
                TextColumn::make('realized_revenue')
                    ->label('Realized Revenue (All Time)')
                    ->money('NGN')
                    ->state(function (User $record): int {
                        return \Src\Order\Domain\Models\Invoice::query()
                            ->where('user_id', $record->id)
                            ->where('status', 'paid')
                            ->sum('total');
                    })
                    ->sortable(),

                TextColumn::make('estimated_profit')
                    ->label('Estimated Profit')
                    ->money('NGN'),
            ])
            ->filters([
                SelectFilter::make('pharmacy')
                    ->relationship('pharmacy', 'name'),
            ]);
    }
}
