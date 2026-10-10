<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Symfony\Component\Mime\Email;

/** Notifikasi ke pemesan bahwa bookingnya dikonfirmasi / ditolak / selesai. */
class BookingStatusUpdated extends Mailable
{
    private const LABELS = [
        'CONFIRMED' => 'dikonfirmasi',
        'REJECTED' => 'ditolak',
        'COMPLETED' => 'selesai',
    ];

    public function __construct(public Booking $booking, public string $recipientName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Anda '.$this->statusLabel(),
            using: [
                fn (Email $message) => $message->embedFromPath(public_path('piellot-logo.png'), 'piellot-logo', 'image/png'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-status',
            with: [
                'statusLabel' => $this->statusLabel(),
                'url' => route('bookings.show', $this->booking),
            ],
        );
    }

    private function statusLabel(): string
    {
        return self::LABELS[$this->booking->status] ?? strtolower($this->booking->status);
    }
}
