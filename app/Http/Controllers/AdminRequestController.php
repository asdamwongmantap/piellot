<?php

namespace App\Http\Controllers;

use App\Models\CompanyAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menggantikan action request_company_admin dan set_admin_request_status
 * pada app/api/action/route.ts.
 */
class AdminRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'requested_name' => ['required', 'string', 'min:2', 'max:90'],
            'requested_email' => ['required', 'email', 'max:160'],
        ]);

        $email = strtolower($validated['requested_email']);

        if (User::whereRaw('lower(email) = ?', [$email])->exists()) {
            return back()->withErrors(['requested_email' => 'Email tersebut sudah terhubung ke akun PIELLOT.'])->withInput();
        }

        if (CompanyAdminRequest::whereRaw('lower(requested_email) = ?', [$email])->where('status', 'PENDING')->exists()) {
            return back()->withErrors(['requested_email' => 'Permintaan untuk email tersebut masih menunggu approval.'])->withInput();
        }

        CompanyAdminRequest::create([
            'company_id' => $user->company_id,
            'requested_name' => $validated['requested_name'],
            'requested_email' => $email,
            'requested_by_user_id' => $user->id,
            'status' => 'PENDING',
        ]);

        return back()->with('success', 'Permintaan admin berhasil dikirim.');
    }

    public function setStatus(Request $request, CompanyAdminRequest $adminRequest)
    {
        $validated = $request->validate(['status' => ['required', 'in:APPROVED,REJECTED']]);

        if ($adminRequest->status !== 'PENDING') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        if ($validated['status'] === 'APPROVED') {
            $existing = User::whereRaw('lower(email) = ?', [strtolower($adminRequest->requested_email)])->first();
            if ($existing && ($existing->isAdmin() || ($existing->company_id && $existing->company_id !== $adminRequest->company_id))) {
                return back()->with('error', 'Email tersebut sudah digunakan pada akun lain.');
            }
        }

        $adminRequest->update(['status' => $validated['status']]);

        return back()->with(
            'success',
            $validated['status'] === 'APPROVED' ? 'Admin tambahan disetujui.' : 'Permintaan ditolak.'
        );
    }
}
