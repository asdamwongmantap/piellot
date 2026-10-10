<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

/** Notifikasi ke pemesan bahwa bookingnya approved / ditolak / on going / selesai. */
class BookingStatusUpdated extends Mailable
{
    private const LABELS = [
        'APPROVED' => 'approved',
        'CONFIRMED' => 'approved', // status lama
        'REJECTED' => 'ditolak',
        'ON_GOING' => 'sedang berjalan',
        'COMPLETED' => 'selesai',
        'INVOICING' => 'menunggu pembayaran',
        'PAID' => 'lunas',
    ];

    /** Email ke pemesan: lewat user_id bila ada (> 0), jika tidak lewat pic_name. Gagal kirim diabaikan. */
    public static function notify(Booking $booking): void
    {
        $booking->load(['company', 'vehicle']);

        if ($booking->user_id > 0) {
            $recipients = User::where('id', $booking->user_id)->get();
        } else {
            $pics = User::where('role', 'PIC')->where('status', 'ACTIVE')->where('company_id', $booking->company_id)->get();
            $recipients = $pics->where('name', $booking->pic_name);
            $recipients = $recipients->isNotEmpty() ? $recipients : $pics;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new self($booking, $recipient->name));
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email status booking ke {$recipient->email}: ".$e->getMessage());
            }
        }
    }

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
