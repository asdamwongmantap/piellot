<x-mail::message>
# Booking Anda {{ $statusLabel }}

Halo {{ $recipientName }},

Booking untuk armada **{{ $booking->vehicle->plate }}** pada **{{ $booking->booking_date->translatedFormat('l, d F Y') }}** ({{ $booking->package_label }}) telah **{{ $statusLabel }}**.

<x-mail::button :url="$url">
Lihat Detail Booking
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
