<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Src\Patient\Domain\Models\Patient;

class PharmacistAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Patient $patient) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Meet Your Personal Careflux Pharmacist!');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.patient.pharmacist-assigned');
    }
}
