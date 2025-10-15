<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;

class EditTeamTask extends EditRecord
{
    protected static string $resource = TeamTaskResource::class;

    /**
     * This hook is the definitive fix. It prepares the data from the database
     * to be correctly loaded into our multi-select form field.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->getRecord()->load('pharmacyProducts');
        $data['product_ids'] = $this->getRecord()->pharmacyProducts->pluck('id')->all();
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update([
                'task_definition_id' => $data['task_definition_id'],
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'due_at' => $data['due_at'],
            ]);
            if (isset($data['product_ids'])) {
                $record->pharmacyProducts()->sync($data['product_ids']);
            } else {
                $record->pharmacyProducts()->sync([]);
            }
            return $record;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
