<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

/** Halaman "Akun" milik PIC (renderAccount pada app/piellot-app.tsx): identitas + ubah kata sandi. */
class AccountController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('company');

        return Inertia::render('Account/Index', [
            'account' => ['name' => $user->name, 'email' => $user->email],
            'company' => $user->company?->name,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [], [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ]);

        $user = $request->user();
        $user->update(['password' => Hash::make($validated['password'])]);

        try {
            Mail::to($user->email)->send(new PasswordChanged($user, now()->translatedFormat('d F Y H:i')));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email konfirmasi ganti password: '.$e->getMessage());
        }

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
