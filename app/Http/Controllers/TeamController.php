<?php

namespace App\Http\Controllers;

use App\Models\CompanyAdminRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Halaman "Admin PT" milik PIC: daftar sesama PIC di perusahaan yang sama
 * + riwayat permintaan admin tambahan.
 */
class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Team/Index', [
            'companyAdmins' => User::where('company_id', $user->company_id)->where('role', 'PIC')->orderBy('created_at')->get(),
            'adminRequests' => CompanyAdminRequest::where('company_id', $user->company_id)->latest()->get(),
        ]);
    }
}
