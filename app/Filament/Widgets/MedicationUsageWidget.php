<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Src\Medication\Domain\Models\Medication;

class MedicationUsageWidget extends TableWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top Medications by Quantity Sold';

    public function table(Table $table): Table
    {
        return $table
            // We use a raw query for this complex aggregation for maximum performance.
            ->query(
                Medication::query()
                    ->select([
                        'medications.id',
                        'medications.name',
                        DB::raw('SUM(invoice_items.quantity) as total_quantity_sold'),
                        DB::raw('COUNT(DISTINCT invoices.patient_id) as unique_patients'),
                    ])
                    ->join('medication_variants', 'medications.id', '=', 'medication_variants.medication_id')
                    ->join('pharmacy_products', 'medication_variants.id', '=', 'pharmacy_products.medication_variant_id')
                    ->join('invoice_items', 'pharmacy_products.id', '=', 'invoice_items.pharmacy_product_id')
                    ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.status', 'paid') // Only count paid orders
                    ->groupBy('medications.id', 'medications.name')
                    ->orderByDesc('total_quantity_sold')
            )
            ->columns([
                TextColumn::make('name')->label('Medication'),
                TextColumn::make('total_quantity_sold')->label('Total Units Sold')->numeric()->sortable(),
                TextColumn::make('unique_patients')->label('Unique Patients')->numeric()->sortable(),
            ])
            ->paginated(false); // Show the top results without pagination
    }
}
