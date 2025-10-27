<?php

namespace App\Listeners;

use App\Events\UserLeveledUp;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Shared\Infrastructure\Services\TelegramService;

class SendLevelUpNotification implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(UserLeveledUp $event): void
    {
        $user = $event->user;
        $newLevel = $event->newLevel->value;

        // Filament Notification
        Notification::make()
            ->title("Congratulations! You've Reached {$newLevel} Level!")
            ->body('Your hard work has paid off. You now have access to new benefits and rewards.')
            ->success()->icon('heroicon-o-sparkles')
            ->sendToDatabase($user);

        // Telegram Notification
        $message = "🏆 *Level Up\!* 🎉\n\nCongratulations, *{$user->name}* SICK!\n\nYou have officially reached the *{$newLevel}* level on Careflux\. Keep up the amazing work\! ";
        $this->telegramService->sendMessageToUser($user, $message);
    }
}
