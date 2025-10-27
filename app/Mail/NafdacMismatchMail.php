<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class NafdacMismatchMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PharmacyProduct $product,
        public string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Action Required: NAFDAC Verification Alert for Your Product');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.pharmacy.nafdac-mismatch');
    }
}
