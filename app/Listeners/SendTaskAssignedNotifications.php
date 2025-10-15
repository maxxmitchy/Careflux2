<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Mail\TaskAssignedMail;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue; // For email
use Illuminate\Support\Facades\Mail; // We will create this
use Src\Shared\Infrastructure\Services\TelegramService;

class SendTaskAssignedNotifications implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(TaskAssigned $event): void
    {
        $task = $event->task->load(['assignee', 'taskDefinition']);
        $assignee = $task->assignee;

        // 1. Send Filament Notification
        Notification::make()
            ->title('New Task Assigned: '.$task->taskDefinition->name)
            ->body('A new task requires your attention. Please check your dashboard.')
            ->warning()
            ->sendToDatabase($assignee);

        // 2. Send Telegram Notification
        $message = "🔔 *New Task Assigned*\n\n".
                   "A new task, '{$task->taskDefinition->name}', has been assigned to you.";
        $this->telegramService->sendMessageToUser($assignee, $message);

        // 3. Send Email Notification
        if ($assignee->email) {
            Mail::to($assignee->email)->send(new TaskAssignedMail($task));
        }
    }
}
