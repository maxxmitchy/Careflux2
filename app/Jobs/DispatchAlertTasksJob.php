<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Src\Gamification\Domain\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Gamification\Domain\Models\TaskDefinition;
use src\Pharmacovigilance\Domain\Models\ProductAlert;
use App\Filament\Pharmacy\Resources\Tasks\TaskResource;
use Src\Shared\Infrastructure\Services\TelegramService;

class DispatchAlertTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ProductAlert $alert) {}

    public function handle(TelegramService $telegramService): void
    {
        $taskDefinition = TaskDefinition::where('key', 'PRODUCT_INTEGRITY_CHECK')->firstOrFail();
        $affectedProducts = PharmacyProduct::query()
            ->whereHas('medicationVariant', fn ($q) => $q->where('medication_id', $this->alert->medication_id))
            ->with('user')
            ->get();

        $productsByPharmacist = $affectedProducts->groupBy('user_id');

        foreach ($productsByPharmacist as $pharmacistId => $products) {
            $pharmacist = $products->first()->user;
            if (! $pharmacist) {
                continue;
            }

            $task = Task::create([
                'task_definition_id' => $taskDefinition->id,
                'assigned_to_user_id' => $pharmacist->id,
                'created_by_user_id' => $this->alert->created_by_user_id,
                'title' => "[{$this->alert->severity}] - {$this->alert->title}",
                'description' => $this->alert->instructions,
                'status' => 'pending',
                'due_at' => now()->addHours(24),
            ]);

            $task->pharmacyProducts()->attach($products->pluck('id'));

            // --- NOTIFICATION IMPLEMENTATION ---
            // 1. Filament Database Notification
            Notification::make()
                ->title("URGENT TASK: {$this->alert->title}")
                ->body('A high-priority product integrity check requires your immediate attention.')
                ->danger() // Use danger color for urgency
                ->actions([Action::make('view_task')->label('View Task')->url(TaskResource::getUrl('index'))])
                ->sendToDatabase($pharmacist);

            // 2. Telegram Notification
            if ($pharmacist->telegram_chat_id) {
                $telegramMessage = "🚨 *URGENT TASK ASSIGNED*\n\n"
                                 ."*Alert:* {$this->alert->title}\n"
                                 .'*Severity:* '.ucfirst($this->alert->severity)."\n\n"
                                 .'Please log in to your Careflux dashboard immediately to view instructions and complete the required action.';

                $telegramService->sendMessageToUser($pharmacist, $telegramMessage);
            }
            // --- END NOTIFICATION IMPLEMENTATION ---
        }
    }
}
