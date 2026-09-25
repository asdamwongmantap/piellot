<?php

namespace App\Http\Controllers;

use App\Models\CompanyAdminRequest;
use App\Models\User;
use Inertia\Inertia;

/**
 * Halaman "Akses PIC" untuk Admin Fleet (renderAccess pada app/piellot-app.tsx):
 * daftar email akun PIC beserta undangan admin tambahan yang sudah disetujui
 * tetapi belum mendaftar. Kata sandi tidak pernah ditampilkan.
 */
class PicAccessController extends Controller
{
    public function index()
    {
        $users = User::with('company')->where('role', 'PIC')->orderBy('created_at')->get();
        $registeredEmails = $users->pluck('email')->map(fn ($email) => strtolower($email))->all();

        $accesses = $users->map(fn (User $user) => [
            'id' => "user-{$user->id}",
            'name' => $user->name,
            'email' => $user->email,
            'company_name' => $user->company?->name ?? '—',
            'status' => $user->status,
        ]);

        $invites = CompanyAdminRequest::with('company')
            ->where('status', 'APPROVED')
            ->orderBy('created_at')
            ->get()
            ->reject(fn ($invite) => in_array(strtolower($invite->requested_email), $registeredEmails, true))
            ->map(fn ($invite) => [
                'id' => "invite-{$invite->id}",
                'name' => $invite->requested_name,
                'email' => $invite->requested_email,
                'company_name' => $invite->company?->name ?? '—',
                'status' => 'INVITED',
            ]);

        return Inertia::render('Access/Index', [
            'accesses' => $accesses->concat($invites)->values(),
            'loginUrl' => route('login'),
        ]);
    }
}
