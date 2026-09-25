<?php

namespace App\Http\Controllers;

use App\Models\CompanyAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

/**
 * Menggantikan `app/chatgpt-auth.ts` pada source aslinya.
 * Versi Next.js memakai header identitas dari ChatGPT Apps. Di Laravel kita
 * memakai autentikasi email + password standar bawaan Laravel.
 *
 * Aturan akun pertama: pengguna PERTAMA yang mendaftar di sistem otomatis
 * menjadi ADMIN aktif (persis seperti logika `ensureAccount` di
 * app/api/bootstrap/route.ts). Pengguna berikutnya mendaftar sebagai calon
 * PIC dan wajib mendaftarkan perusahaannya lebih dulu (lihat CompanyController).
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:90'],
            'email' => ['required', 'string', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Akun pertama di sistem = Admin Fleet. Sisanya menjadi calon PIC.
        $isFirstAccount = User::query()->count() === 0;

        // Jika email ini sebelumnya diundang lewat "admin tambahan" dan sudah
        // disetujui Admin (lihat AdminRequestController), langsung hubungkan
        // ke perusahaan tersebut sebagai PIC aktif — meniru ensureAccount()
        // pada app/api/bootstrap/route.ts versi aslinya.
        $approvedInvite = CompanyAdminRequest::whereRaw('lower(requested_email) = ?', [strtolower($validated['email'])])
            ->where('status', 'APPROVED')
            ->latest()
            ->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $isFirstAccount ? 'ADMIN' : 'PIC',
            'company_id' => $approvedInvite?->company_id,
            'status' => $isFirstAccount ? 'ACTIVE' : ($approvedInvite ? 'ACTIVE' : 'PENDING'),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.'])->onlyInput('email');
        }

        $user->update(['password' => $validated['password']]);

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
