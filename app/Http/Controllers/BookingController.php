<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Menggantikan action create_booking dan set_booking_status
 * pada app/api/action/route.ts.
 *
 * Admin Fleet melihat SEMUA booking, PIC hanya melihat booking milik
 * perusahaannya sendiri (persis seperti query bootstrap di aslinya).
 */
class BookingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = Booking::with(['company', 'vehicle'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('company_id', $user->company_id))
            ->latest()
            ->get();

        return Inertia::render('Bookings/Index', ['bookings' => $bookings]);
    }

    public function create()
    {
        return Inertia::render('Bookings/Create', [
            'vehicles' => Vehicle::where('status', 'AVAILABLE')->orderBy('plate')->get(),
            'packages' => Booking::PACKAGES,
            'driverRate' => Booking::DRIVER_RATE,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'package_code' => ['required', 'in:4h,8h,24h'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'load_ton' => ['required', 'numeric', 'min:0.1'],
            'destination' => ['required', 'string', 'max:160'],
            'passengers' => ['required', 'integer', 'min:0', 'max:10'],
            'need_driver' => ['nullable', 'boolean'],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if (! $vehicle->isAvailable()) {
            throw ValidationException::withMessages(['vehicle_id' => 'Armada tersebut sedang tidak tersedia.']);
        }

        if ($validated['load_ton'] > $vehicle->capacity_ton) {
            throw ValidationException::withMessages([
                'load_ton' => "Muatan melebihi kapasitas armada {$vehicle->capacity_ton} ton.",
            ]);
        }

        $conflict = Booking::where('vehicle_id', $vehicle->id)
            ->where('booking_date', $validated['booking_date'])
            ->where('status', '!=', 'REJECTED')
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['booking_date' => 'Armada sudah dibooking pada tanggal tersebut.']);
        }

        $needDriver = $request->boolean('need_driver');
        $rentalFee = Booking::PACKAGES[$validated['package_code']]['rate'];
        $driverFee = $needDriver ? Booking::DRIVER_RATE : 0;

        Booking::create([
            'company_id' => $user->company_id,
            'pic_name' => $user->name,
            'vehicle_id' => $vehicle->id,
            'package_code' => $validated['package_code'],
            'booking_date' => $validated['booking_date'],
            'load_ton' => $validated['load_ton'],
            'destination' => $validated['destination'],
            'passengers' => $validated['passengers'],
            'need_driver' => $needDriver,
            'status' => 'PENDING',
            'rental_fee' => $rentalFee,
            'driver_fee' => $driverFee,
            'other_fee' => 0,
            'total_fee' => $rentalFee + $driverFee,
            'paid' => false,
        ]);

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dikirim.');
    }

    public function show(Booking $booking)
    {
        $this->authorizeView($booking);
        $booking->load(['company', 'vehicle']);

        return Inertia::render('Bookings/Show', ['booking' => $booking]);
    }

    public function setStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate(['status' => ['required', 'in:CONFIRMED,REJECTED,COMPLETED']]);

        $allowedTransition =
            ($booking->status === 'PENDING' && in_array($validated['status'], ['CONFIRMED', 'REJECTED']))
            || ($booking->status === 'CONFIRMED' && $validated['status'] === 'COMPLETED');

        if (! $allowedTransition) {
            return back()->with('error', 'Perubahan status booking tersebut tidak diperbolehkan.');
        }

        $booking->update(['status' => $validated['status']]);

        return back()->with('success', 'Status booking diperbarui.');
    }

    private function authorizeView(Booking $booking): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $booking->company_id !== $user->company_id) {
            abort(403);
        }
    }
}
