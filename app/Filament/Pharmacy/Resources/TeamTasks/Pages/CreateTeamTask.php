<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateTeamTask extends CreateRecord
{
    protected static string $resource = TeamTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The creator is the logged-in manager
        $data['created_by_user_id'] = Auth::id();

        return $data;
    }

    /**
     * We override the creation handler to manually sync the products.
     * This is necessary because 'product_ids' is not a direct column on the 'tasks' table.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Extract product_ids from the form data
            $productIds = $data['product_ids'] ?? [];

            // 2. Remove it from data so Task::create() doesn't fail (if strict) or ignore it
            unset($data['product_ids']);

            // 3. Create the Task record
            $record = static::getModel()::create($data);

            // 4. Manually sync the relationship if products were selected
            if (! empty($productIds)) {
                // Ensure your Task model has 'pharmacyProducts()' relationship defined
                $record->pharmacyProducts()->sync($productIds);
            }

            return $record;
        });
    }
}
