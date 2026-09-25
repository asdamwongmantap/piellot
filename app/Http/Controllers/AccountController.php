<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
