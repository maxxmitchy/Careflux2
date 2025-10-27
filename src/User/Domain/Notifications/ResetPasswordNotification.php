<?php

namespace Src\User\Domain\Notifications;

use App\Mail\PasswordResetMail; // <-- Import our new Mailable
use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ResetPasswordNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $panelId = $this->getPanelIdForUser($notifiable);
        $resetUrl = route("filament.{$panelId}.auth.password.reset", [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new PasswordResetMail($resetUrl))->to($notifiable->email);
    }

    /**
     * Determines which panel's reset link to generate based on user role.
     */
    private function getPanelIdForUser($user): string
    {
        if ($user->is_pharmacist) {
            return 'pharmacy';
        }
        if ($user->is_technician) {
            return 'technician';
        }
        if ($user->is_admin) {
            return 'admin';
        }

        return 'patient'; // Default to patient
    }
}
