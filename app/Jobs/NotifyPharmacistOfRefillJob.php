<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Src\Shared\Domain\Models\User;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Src\Gamification\Domain\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Shared\Infrastructure\Services\TelegramService;
use App\Filament\Pharmacy\Resources\Tasks\TaskResource;

class NotifyPharmacistOfRefillJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $pharmacistId,
        public Collection $prescriptions,
        public int $daysUntilDue
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        $pharmacist = User::find($this->pharmacistId);
        if (!$pharmacist) {
            return;
        }

        // Group prescriptions by patient for consolidated task descriptions.
        $prescriptionsByPatient = $this->prescriptions->groupBy('patient_id');
        $taskDefinition = TaskDefinition::where('key', 'PATIENT_FOLLOW_UP_REFILL')->first();

        foreach ($prescriptionsByPatient as $patientId => $patientPrescriptions) {
            $patient = $patientPrescriptions->first()->patient;
            $medicationNames = $patientPrescriptions->pluck('medication.name')->implode(', ');
            
            // Create a single, consolidated task for this patient's refills.
            $task = Task::create([
                'task_definition_id' => $taskDefinition?->id,
                'assigned_to_user_id' => $pharmacist->id,
                'created_by_user_id' => 1, // System-generated
                'subjectable_id' => $patient->id,
                'subjectable_type' => $patient->getMorphClass(),
                'title' => "Follow up with {$patient->full_name} for refill",
                'description' => "Refill due in {$this->daysUntilDue} days for: {$medicationNames}.",
                'due_at' => $patientPrescriptions->first()->refill_due_date,
            ]);

            // 1. Send Filament Database Notification
            Notification::make()
                ->title("Refill Reminder: {$patient->full_name}")
                ->body("A refill is due in {$this->daysUntilDue} days for {$medicationNames}.")
                ->warning()
                ->actions([
                    Action::make('view_task')
                        ->label('View Task')
                        ->url(TaskResource::getUrl('index'))
                        // ->url(route('filament.pharmacy.resources.tasks.edit', ['record' => $task])) // Assuming a Task resource
                ])
                ->sendToDatabase($pharmacist);
            
            // 2. Send Telegram Notification (using your queueable service)
            if ($pharmacist->telegram_chat_id) {
                $telegramMessage = "🔔 *New Refill Task*\n\n"
                                 . "*Patient:* {$patient->full_name}\n"
                                 . "*Due:* In {$this->daysUntilDue} days\n"
                                 . "*Medications:* {$medicationNames}\n\n"
                                 . "Please log in to your dashboard to view and complete the task.";
                
                $telegramService->sendMessageToUser($pharmacist, $telegramMessage);
            }
        }
    }
}