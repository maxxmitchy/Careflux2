<?php

namespace App\Jobs;

use App\Filament\Pharmacy\Resources\Tasks\TaskResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Gamification\Domain\Models\Task;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Medication\Domain\Models\Medication;
use Src\Order\Domain\Models\Invoice;
use Src\Shared\Infrastructure\Services\TelegramService;

class CreateCounselingTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  \Src\Order\Domain\Models\Invoice  $invoice  The completed order that triggered this job.
     * @param  \Src\Medication\Domain\Models\Medication  $medication  The specific medication requiring counseling.
     */
    public function __construct(public Invoice $invoice, public Medication $medication) {}

    /**
     * Execute the job.
     * This will create a task and notify the assigned pharmacist.
     */
    public function handle(TelegramService $telegramService): void
    {
        $patient = $this->invoice->patient;
        $pharmacist = $patient->pharmacist;

        // Guard: Do nothing if the patient is not assigned to a pharmacist.
        if (! $pharmacist) {
            return;
        }

        $counselingPoints = $this->medication->counselingPoints;

        // Guard: Do nothing if there are no counseling points for this medication.
        if ($counselingPoints->isEmpty()) {
            return;
        }

        $taskDefinition = TaskDefinition::where('key', 'PATIENT_COUNSELING_FOLLOW_UP')->firstOrFail();

        // Prepare the detailed description for the task, including message templates.
        $description = "Please send the following critical advice to {$patient->full_name} regarding their new medication, {$this->medication->name}. You can copy and paste these messages:\n\n";
        foreach ($counselingPoints as $point) {
            $templatedMessage = str_replace(
                ['{patient_name}', '{medication_name}'],
                [$patient->full_name, $this->medication->name],
                $point->message_template
            );
            $description .= "• {$templatedMessage}\n";
        }

        // Create the actionable task in the database.
        $task = Task::create([
            'task_definition_id' => $taskDefinition->id,
            'assigned_to_user_id' => $pharmacist->id,
            'created_by_user_id' => 1, // System-generated task
            'subjectable_id' => $patient->id,
            'subjectable_type' => $patient->getMorphClass(),
            'title' => "Counseling Follow-up for {$this->medication->name}",
            'description' => $description,
            'status' => 'pending',
            'due_at' => now()->addHours(24),
        ]);

        // --- NOTIFICATION IMPLEMENTATION ---

        // 1. Send Filament Database Notification for in-app alerting.
        Notification::make()
            ->title('New Counseling Task Assigned')
            ->body("A task to provide counseling to {$patient->full_name} for the medication '{$this->medication->name}' has been created.")
            ->warning() // Use 'warning' color to signify it requires action.
            ->actions([
                Action::make('view_task')
                    ->label('View Task')
                    ->url(TaskResource::getUrl('index')) // Links to the main task list.
                    ->button(),
            ])
            ->sendToDatabase($pharmacist);

        // 2. Send Telegram Notification for immediate, out-of-app alerting.
        if ($pharmacist->telegram_chat_id) {
            $telegramMessage = "⚠️ *New Clinical Task*\n\n"
                             ."*Action:* Patient Counseling\n"
                             ."*Patient:* {$patient->full_name}\n"
                             ."*Medication:* {$this->medication->name}\n\n"
                             ."A new task requires your attention\. Please log in to your Careflux dashboard to view details and send the required information.";

            $telegramService->sendMessageToUser($pharmacist, $telegramMessage);
        }

        // --- END NOTIFICATION IMPLEMENTATION ---
    }
}
