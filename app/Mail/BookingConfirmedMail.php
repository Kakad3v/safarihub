<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingConfirmedMail extends Mailable
{
    public function __construct(public Booking $booking, public Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your SafariHub booking ' . $this->booking->reference . ' is confirmed');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.booking-confirmed', text: 'mail.booking-confirmed-text');
    }
}
