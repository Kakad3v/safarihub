<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginCodeMail extends Mailable
{
    public function __construct(public string $code, public string $link) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your SafariHub sign-in code');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.login-code');
    }
}
