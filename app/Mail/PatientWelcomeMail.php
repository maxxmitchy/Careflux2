<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Src\Shared\Domain\Models\User; // <-- Implement ShouldQueue

class PatientWelcomeMail extends Mailable implements ShouldQueue // <-- Implement ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $token = null // Token is optional
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->token
            ? 'Welcome to Careflux - Please Set Your Password'
            : 'Welcome to the Careflux Family!';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.patient-welcome', // Use Markdown for beautiful, responsive emails
            with: [
                'url' => $this->token
                    ? route('filament.patient.auth.password.reset', [
                        'token' => $this->token,
                        'email' => $this->user->getEmailForPasswordReset(),
                    ])
                    : route('filament.patient.auth.login'),
            ]
        );
    }
}
