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
use Src\Content\Domain\Models\CounselingJourneyStep;
use Src\Gamification\Domain\Models\Task;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Infrastructure\Services\TelegramService;

class CreateDripCounselingTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Patient $patient, public CounselingJourneyStep $step) {}

    public function handle(TelegramService $telegramService): void
    {
        $pharmacist = $this->patient->pharmacist;
        if (! $pharmacist) {
            return;
        }

        $taskDefinition = TaskDefinition::where('key', 'PATIENT_COUNSELING_FOLLOW_UP')->firstOrFail();

        // Personalize the message template
        $messageToSend = str_replace(
            ['{patient_name}', '{medication_name}'],
            [$this->patient->full_name, $this->step->counselingJourney->medication->name],
            $this->step->message_template
        );

        $task = Task::create([
            'task_definition_id' => $taskDefinition->id,
            'assigned_to_user_id' => $pharmacist->id,
            'created_by_user_id' => null, // System-generated
            'subjectable_id' => $this->patient->id,
            'subjectable_type' => $this->patient->getMorphClass(),
            'title' => "Day {$this->step->day_to_send} counseling for {$this->patient->full_name}",
            'description' => "Please send the following message to your patient:\n\n\"{$messageToSend}\"",
            'due_at' => now()->endOfDay(),
        ]);

        // Send Filament Notification
        Notification::make()
            ->title('Daily Counseling Task')
            ->body("A new counseling message is ready to be sent to {$this->patient->full_name}.")
            ->info()
            ->actions([Action::make('view_task')->label('View Task')->url(TaskResource::getUrl('index'))])
            ->sendToDatabase($pharmacist);

        // Send Telegram Notification
        if ($pharmacist->telegram_chat_id) {
            $telegramMessage = "💡 *New Counseling Task*\n\n"
                             ."A pre-written daily check-in message for *{$this->patient->full_name}* is ready. Please log in to review and send.";
            $telegramService->sendMessageToUser($pharmacist, $telegramMessage);
        }
    }
}
