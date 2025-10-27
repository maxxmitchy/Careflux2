<?php

namespace Src\User\Domain\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Shared\Domain\Models\User;

class NewUserRegistered extends \Illuminate\Notifications\Notification implements ShouldQueue
{
    use Queueable;

    protected User $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // For displaying in the Filament panel
    }

    /**
     * Get the array representation of the notification for storage.
     * This uses the Filament Notification builder for a rich, interactive notification.
     */
    public function toArray(object $notifiable): array
    {
        $pharmacyName = $this->newUser->pharmacy?->name ?? 'a new pharmacy';

        return Notification::make()
            ->title('New Pharmacist Requires Verification')
            ->body("{$this->newUser->name} from {$pharmacyName} has registered and requires approval.")
            ->icon('heroicon-o-user-plus')
            ->warning() // Use a warning color to indicate action is needed
            ->actions([
                Action::make('view_user')
                    ->label('View & Approve User')
                    ->url(fn (): string => route('filament.admin.resources.users.edit', ['record' => $this->newUser->id]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
