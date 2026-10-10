<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyAdminRequest;
use App\Models\Message;
use App\Models\RentalPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Menggantikan action reset_transaction_data pada app/api/action/route.ts,
 * ditambah pengaturan paket durasi (tarif sewa, tarif driver, denda) yang sebelumnya
 * berupa konstanta di App\Models\Booking.
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
            'packages' => RentalPackage::orderBy('sort_order')->get(),
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

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:8', 'alpha_dash', 'unique:rental_packages,code'],
            'label' => ['required', 'string', 'max:60'],
            'area' => ['required', 'in:CITY,OUTSIDE'],
            'window_label' => ['required', 'string', 'max:60'],
            'start_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:72'],
            'rate' => ['required', 'integer', 'min:0'],
            'driver_rate' => ['required', 'integer', 'min:0'],
            'late_fee' => ['required', 'integer', 'min:0'],
            'tolerance_hours' => ['required', 'integer', 'min:0', 'max:24'],
        ], [], [
            'code' => 'kode paket',
            'label' => 'nama paket',
            'area' => 'area layanan',
            'window_label' => 'jam operasional',
            'start_hour' => 'jam mulai',
            'duration_hours' => 'durasi',
            'rate' => 'tarif',
            'driver_rate' => 'tarif driver',
            'late_fee' => 'denda keterlambatan',
            'tolerance_hours' => 'toleransi',
        ]);

        RentalPackage::create($validated + [
            'sort_order' => (RentalPackage::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Paket durasi berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, RentalPackage $rentalPackage)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'area' => ['required', 'in:CITY,OUTSIDE'],
            'window_label' => ['required', 'string', 'max:60'],
            'rate' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'label' => 'nama paket',
            'area' => 'area layanan',
            'window_label' => 'jam operasional',
            'start_hour' => 'jam mulai',
            'duration_hours' => 'durasi',
            'rate' => 'tarif',
            'driver_rate' => 'tarif driver',
            'late_fee' => 'denda keterlambatan',
            'tolerance_hours' => 'toleransi',
        ]);

        $rentalPackage->update($validated);

        return back()->with('success', 'Paket durasi berhasil diperbarui.');
    }

    public function destroyPackage(RentalPackage $rentalPackage)
    {
        if ($rentalPackage->bookings()->exists()) {
            return back()->with('error', 'Paket memiliki riwayat booking dan belum dapat dihapus. Nonaktifkan saja paket ini.');
        }

        $rentalPackage->delete();

        return back()->with('success', 'Paket durasi dihapus.');
    }
}
