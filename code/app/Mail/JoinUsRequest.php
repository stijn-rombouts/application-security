<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class JoinUsRequest extends Mailable
{
    public function __construct(
        public string $fullName,
        public string $emailAddress,
        public string $heardFrom,
        public string $joinUs,
        public string $organisation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nieuw lidmaatschapsverzoek van " . $this->fullName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.join-us-request',
        );
    }
}
