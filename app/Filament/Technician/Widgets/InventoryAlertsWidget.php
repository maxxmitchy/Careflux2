<?php

namespace App\Filament\Technician\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class InventoryAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Inventory Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PharmacyProduct::query()
                    ->where('pharmacy_id', Filament::auth()->user()->pharmacy_id)
                    ->where('stock', '<=', 10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Product'),
                Tables\Columns\TextColumn::make('stock')->label('Current Stock'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'danger')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? 'Low Stock' : 'Out of Stock'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export for Procurement')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Inventory_Restock_Request_'.now()->format('Y-m-d')),
                    ])->disabled(fn ($livewire) => $livewire->getTableRecords()->isEmpty()),
            ])
            ->paginated(false)
            ->emptyStateHeading('No inventory alerts')
            ->emptyStateDescription('Products with low or zero stock will appear here.');
    }
}
