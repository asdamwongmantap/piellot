<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Halaman "Jadwal" (renderSchedule pada app/piellot-app.tsx): kalender armada
 * yang menandai tanggal berisi booking. Admin melihat semua booking, PIC hanya
 * milik perusahaannya. Booking yang ditolak tidak ditampilkan.
 */
class ScheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = Booking::with(['company', 'vehicle'])
            ->where('status', '!=', 'REJECTED')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('company_id', $user->company_id))
            ->orderBy('booking_date')
            ->get();

        return Inertia::render('Schedule/Index', ['bookings' => $bookings]);
    }
}
