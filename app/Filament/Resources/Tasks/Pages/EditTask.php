<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Use the new, correct relationship name
        $this->getRecord()->load('pharmacyProducts');
        $data['product_ids'] = $this->getRecord()->pharmacyProducts->pluck('id')->all();

        return $data;
    }

    /**
     * This hook handles saving the data from the form back to the database.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // 1. Update the main Task record fields.
            $record->update([
                'task_definition_id' => $data['task_definition_id'],
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'due_at' => $data['due_at'],
                'status' => $data['status'],
            ]);

            // 2. "Sync" the many-to-many relationship for the subjects.
            //    The sync method is the perfect tool for this. It automatically
            //    adds new entries, removes old ones, and leaves existing ones untouched.
            if (isset($data['product_ids'])) {
                // Use the new, correct relationship name
                $record->pharmacyProducts()->sync($data['product_ids']);
            }

            return $record;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
