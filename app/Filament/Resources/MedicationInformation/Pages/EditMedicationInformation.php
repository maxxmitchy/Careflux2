<?php

namespace App\Filament\Resources\MedicationInformation\Pages;

use App\Filament\Resources\MedicationInformation\MedicationInformationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicationInformation extends EditRecord
{
    protected static string $resource = MedicationInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pharmacyProductIds = $this->getRecord()->pharmacyProducts()->pluck('pharmacy_products.id')->all();
        $relatedProductIds = $this->getRecord()->relatedProducts()->pluck('pharmacy_products.id')->all();

        $data['pharmacyProducts'] = $pharmacyProductIds;
        $data['relatedProducts'] = $relatedProductIds;

        return $data;
    }

    /**
     * This hook runs AFTER the main MedicationInformation record has been updated.
     */
    protected function afterSave(): void
    {
        $this->syncRelationships();
    }

    /**
     * A helper method to sync the many-to-many relationships.
     */
    protected function syncRelationships(): void
    {
        $pharmacyProductIds = $this->data['pharmacyProducts'] ?? [];
        $relatedProductIds = $this->data['relatedProducts'] ?? [];

        $this->getRecord()->pharmacyProducts()->sync($pharmacyProductIds);
        $this->getRecord()->relatedProducts()->sync($relatedProductIds);
    }
}
