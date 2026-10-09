<x-mail::message>
# Kata sandi berhasil diubah

Halo {{ $user->name }},

Kata sandi akun Anda telah diubah pada **{{ $changedAt }}**.

Jika ini bukan Anda, segera atur ulang kata sandi melalui halaman "Lupa password" dan hubungi administrator.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
