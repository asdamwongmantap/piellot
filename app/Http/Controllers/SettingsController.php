<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyAdminRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Menggantikan action reset_transaction_data pada app/api/action/route.ts.
 * Hanya menghapus data TRANSAKSI. Akun Admin Fleet dan armada TIDAK ikut terhapus.
 */
class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'summary' => [
                'companies' => Company::count(),
                'bookings' => Booking::count(),
                'adminRequests' => CompanyAdminRequest::count(),
                'messages' => Message::count(),
                'companyAdmins' => User::where('role', 'PIC')->count(),
            ],
        ]);
    }

    public function reset()
    {
        DB::transaction(function () {
            Message::query()->delete();
            CompanyAdminRequest::query()->delete();
            Booking::query()->delete();
            User::where('role', 'PIC')->delete();
            Company::query()->delete();
        });

        return redirect()->route('settings.index')->with('success', 'Data transaksi berhasil direset.');
    }
}
