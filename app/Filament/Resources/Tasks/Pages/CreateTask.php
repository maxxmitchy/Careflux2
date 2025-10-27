<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();

        return $data;
    }

    /**
     * Override the default creation process to handle the polymorphic subjectable field correctly.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $taskData = [
                'created_by_user_id' => Auth::id(),
                'task_definition_id' => $data['task_definition_id'],
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'due_at' => $data['due_at'],
            ];

            $task = static::getModel()::create($taskData);

            if (! empty($data['product_ids'])) {
                $pivots = [];
                foreach ($data['product_ids'] as $productId) {
                    $pivots[] = [
                        'task_id' => $task->id,
                        'subjectable_id' => $productId,
                        'subjectable_type' => PharmacyProduct::class,
                    ];
                }
                DB::table('task_subjectables')->insert($pivots);
            }

            return $task;
        });
    }
}
