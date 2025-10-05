<?php

namespace Src\Gamification\Application\Actions;

use App\Models\PharmacistReport;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Src\Gamification\Domain\Models\Task;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;
use Src\Shared\Infrastructure\Services\TelegramService;

class AssignTaskAction
{
    public function __construct(private TelegramService $telegramService) {}

    public function execute(TaskDefinition $taskDefinition, User $assignee, Model $subjectable, ?User $admin = null, ?string $dueDate = null): ?Task
    {
        // Prevent assigning duplicate, open tasks for the same subject
        $existingTask = Task::where('task_definition_id', $taskDefinition->id)
            ->where('subjectable_id', $subjectable?->id)
            ->where('subjectable_type', $subjectable?->getMorphClass())
            ->where('assigned_to_user_id', $assignee->id)
            ->where('status', 'pending')
            ->first();

        if ($existingTask) {
            // Task already exists, do not create another.
            return null;
        }

        $task = Task::create([
            'task_definition_id' => $taskDefinition->id,
            'assigned_to_user_id' => $assignee->id,
            'created_by_user_id' => $admin?->id ?? $assignee->id, // If no admin, assign to self
            'subjectable_id' => $subjectable->id,
            'subjectable_type' => $subjectable->getMorphClass(),
            'due_at' => $dueDate,
        ]);

        // Send Filament Database Notification
        Notification::make()
            ->title('New Task Assigned: '.$taskDefinition->name)
            ->body('A new task requires your attention. Please check your dashboard for details.')
            ->warning()
            ->sendToDatabase($assignee);

        // Send Real-time Telegram Notification
        $subjectDescription = $this->getSubjectDescription($subjectable);
        $message = "🔔 *New Task Assigned*\n\n".
                   "*Task:* {$taskDefinition->name}\n".
                   "*Subject:* {$subjectDescription}\n\n".
                   'This task was assigned to you by an administrator and requires your attention.';

        $this->telegramService->sendMessageToUser($assignee, $message);

        return $task;
    }

    /**
     * Generates a human-readable description of the task's subject.
     */
    private function getSubjectDescription(?Model $subjectable): string
    {
        if ($subjectable instanceof Patient) {
            return "Patient: {$subjectable->full_name}";
        }

        if ($subjectable instanceof PharmacistReport) {
            return "Pharmacist Report from {$subjectable->user->name} (Week ending {$subjectable->week_ending_date->format('M d, Y')})";
        }

        // Add more cases here for other subjectable models like PharmacyProduct, etc.

        return 'a system item';
    }
}
