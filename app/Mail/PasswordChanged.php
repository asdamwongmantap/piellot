<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Symfony\Component\Mime\Email;

/** Konfirmasi ke pengguna bahwa kata sandi akunnya baru saja diubah. */
class PasswordChanged extends Mailable
{
    public function __construct(public User $user, public string $changedAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kata sandi akun Anda telah diubah',
            using: [
                // Logo disematkan ke email (cid:piellot-logo) agar tampil tanpa bergantung pada APP_URL.
                fn (Email $message) => $message->embedFromPath(public_path('piellot-logo.png'), 'piellot-logo', 'image/png'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.password-changed');
    }
}
