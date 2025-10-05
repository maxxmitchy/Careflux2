<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Src\Shared\Domain\Models\User;

class PatientWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to Careflux - Set Your Password');
    }

    public function content(): Content
    {
        // We pass the user and token to the Blade view
        return new Content(view: 'emails.patient-welcome', with: [
            'url' => route('filament.patient.auth.password.reset', [
                'token' => $this->token,
                'email' => $this->user->getEmailForPasswordReset(),
            ]),
        ]);
    }
}
