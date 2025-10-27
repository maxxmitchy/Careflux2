<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Src\Shared\Domain\Models\User;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $invoices
    ) {}

    public function envelope(): Envelope
    {
        $firstInvoiceNumber = $this->invoices->first()?->invoice_number;

        return new Envelope(subject: "Your Careflux Order is Confirmed (#{$firstInvoiceNumber})");
    }

    public function content(): Content
    {
        // We pass all the data the Markdown view will need
        return new Content(markdown: 'emails.order.confirmation');
    }
}
