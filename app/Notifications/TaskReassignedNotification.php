<?php

namespace App\Notifications;

use App\Filament\Pharmacy\Resources\Tasks\TaskResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Src\Gamification\Domain\Models\Task;
use Src\Shared\Domain\Models\User;

class TaskReassignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task, public User $assigner) {}

    public function via(object $notifiable): array
    {
        return ['database']; // For Filament
    }

    public function toArray(object $notifiable): array
    {
        $url = TaskResource::getUrl('index'); // Link to their "My Tasks" page

        return FilamentNotification::make()
            ->title('A Task Has Been Reassigned to You')
            ->body("Task '{$this->task->taskDefinition->name}' was reassigned to you by {$this->assigner->name}.")
            ->info()
            ->actions([
                Action::make('view')->label('View My Tasks')->url($url),
            ])
            ->getDatabaseMessage();
    }
}
