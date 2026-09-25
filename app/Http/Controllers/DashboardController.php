<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Menggantikan bagian "ringkasan" (dashboard) dari app/piellot-app.tsx,
 * dengan data dari app/api/bootstrap/route.ts.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        $bookingsQuery = Booking::with(['company', 'vehicle'])
            ->when(! $isAdmin, fn ($q) => $q->where('company_id', $user->company_id));

        return Inertia::render('Dashboard', [
            'isAdmin' => $isAdmin,
            'recentBookings' => (clone $bookingsQuery)->latest()->take(5)->get(),
            'pendingBookingsCount' => (clone $bookingsQuery)->where('status', 'PENDING')->count(),
            'confirmedBookingsCount' => (clone $bookingsQuery)->where('status', 'CONFIRMED')->count(),
            'totalBookingsCount' => (clone $bookingsQuery)->count(),
            'vehiclesCount' => Vehicle::count(),
            'availableVehiclesCount' => Vehicle::where('status', 'AVAILABLE')->count(),
            'paidRevenue' => (clone $bookingsQuery)->where('paid', true)->sum('total_fee'),
            'unpaidTotal' => (clone $bookingsQuery)->where('paid', false)->where('status', '!=', 'REJECTED')->sum('total_fee'),
            'pendingCompaniesCount' => $isAdmin ? Company::where('status', 'PENDING')->count() : 0,
            'pendingAdminRequestsCount' => $isAdmin ? \App\Models\CompanyAdminRequest::where('status', 'PENDING')->count() : 0,
        ]);
    }
}
