<x-mail::message>
# Booking baru menunggu persetujuan

Ada pengajuan booking baru dari **{{ $booking->company->name }}** (PIC: {{ $booking->pic_name }}).

- **Armada:** {{ $booking->vehicle->plate }}
- **Tanggal:** {{ $booking->booking_date->translatedFormat('l, d F Y') }}
- **Paket:** {{ $booking->package_label }}
- **Tujuan:** {{ $booking->destination }}
- **Estimasi tagihan:** Rp {{ number_format($booking->total_fee, 0, ',', '.') }}

<x-mail::button :url="$url">
Tinjau & Setujui Booking
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
