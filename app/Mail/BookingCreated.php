<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Symfony\Component\Mime\Email;

/** Notifikasi ke admin bahwa ada booking baru yang menunggu persetujuan. */
class BookingCreated extends Mailable
{
    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking baru menunggu persetujuan - '.$this->booking->company->name,
            using: [
                fn (Email $message) => $message->embedFromPath(public_path('piellot-logo.png'), 'piellot-logo', 'image/png'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-created',
            with: ['url' => route('bookings.show', $this->booking)],
        );
    }
}
