<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Menggantikan action `register_company` dan `set_company_status`
 * pada app/api/action/route.ts.
 */
class CompanyController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Companies/Create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'min:3', 'max:90'],
            'pic_name' => ['required', 'string', 'min:2', 'max:90'],
            'phone' => ['required', 'string', 'min:8', 'max:32'],
        ], [], [
            'company_name' => 'nama perusahaan',
            'pic_name' => 'nama PIC',
            'phone' => 'nomor WhatsApp',
        ]);

        if (Company::whereRaw('lower(name) = ?', [strtolower($validated['company_name'])])->exists()) {
            return back()->withErrors(['company_name' => 'Nama perusahaan tersebut sudah terdaftar.'])->withInput();
        }

        $company = Company::create([
            'name' => $validated['company_name'],
            'primary_pic_name' => $validated['pic_name'],
            'phone' => $validated['phone'],
            'email' => strtolower($user->email),
            'status' => 'PENDING',
        ]);

        $user->update([
            'company_id' => $company->id,
            'name' => $validated['pic_name'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('account.waiting');
    }

    public function waiting()
    {
        $user = Auth::user()->load('company');

        return Inertia::render('Companies/Waiting', [
            'account' => $user,
            'company' => $user->company,
        ]);
    }

    public function pendingApprovals()
    {
        return Inertia::render('Companies/Pending', [
            'pendingCompanies' => Company::where('status', 'PENDING')->latest()->get(),
            'pendingAdminRequests' => CompanyAdminRequest::with('company')->where('status', 'PENDING')->latest()->get(),
        ]);
    }

    public function setStatus(Request $request, Company $company)
    {
        $validated = $request->validate(['status' => ['required', 'in:ACTIVE,REJECTED']]);

        if ($company->status !== 'PENDING') {
            return back()->with('error', 'Perusahaan ini sudah diproses sebelumnya.');
        }

        $company->update(['status' => $validated['status']]);
        $company->users()->where('status', 'PENDING')->update(['status' => $validated['status']]);

        return back()->with(
            'success',
            $validated['status'] === 'ACTIVE' ? 'Perusahaan disetujui.' : 'Registrasi ditolak.'
        );
    }
}
