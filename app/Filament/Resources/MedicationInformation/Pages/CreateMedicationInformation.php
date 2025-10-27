<?php

namespace App\Filament\Resources\MedicationInformation\Pages;

use App\Filament\Resources\MedicationInformation\MedicationInformationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicationInformation extends CreateRecord
{
    protected static string $resource = MedicationInformationResource::class;

    /**
     * This hook runs AFTER the main MedicationInformation record has been created.
     */
    protected function afterCreate(): void
    {
        $this->syncRelationships();
    }

    /**
     * A helper method to sync the many-to-many relationships.
     */
    protected function syncRelationships(): void
    {
        // Get the data from the 'pharmacyProducts' and 'relatedProducts' form fields
        $pharmacyProductIds = $this->data['pharmacyProducts'] ?? [];
        $relatedProductIds = $this->data['relatedProducts'] ?? [];

        // Use the sync() method to save the relationships to the pivot tables
        $this->getRecord()->pharmacyProducts()->sync($pharmacyProductIds);
        $this->getRecord()->relatedProducts()->sync($relatedProductIds);
    }
}
