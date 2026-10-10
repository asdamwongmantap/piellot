<?php

namespace App\Http\Controllers;

use App\Mail\BookingCreated;
use App\Mail\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
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
        $packages = RentalPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('code')
            ->map(fn (RentalPackage $package) => [
                'label' => $package->label,
                'area' => $package->area,
                'window' => $package->window_label,
                'rate' => $package->rate,
                'driverRate' => $package->driver_rate,
                'lateFee' => $package->late_fee,
                'toleranceHours' => $package->tolerance_hours,
            ]);

        return Inertia::render('Bookings/Create', [
            'vehicles' => Vehicle::where('status', 'AVAILABLE')->orderBy('plate')->get(),
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $activePackages = RentalPackage::where('is_active', true)->get()->keyBy('code');

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'package_code' => ['required', Rule::in($activePackages->keys())],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'load_ton' => ['required', 'numeric', 'min:0.1'],
            'destination' => ['required', 'string', 'max:160'],
            'passengers' => ['required', 'integer', 'min:0', 'max:10'],
            'need_driver' => ['nullable', 'boolean'],
            'toll_fee' => ['nullable', 'integer', 'min:0', 'max:100000000'],
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
        $package = $activePackages[$validated['package_code']];
        $rentalFee = $package->rate;
        $driverFee = $needDriver ? $package->driver_rate : 0;
        $tollFee = (int) ($validated['toll_fee'] ?? 0); // estimasi tol, cost to cost

        $booking = Booking::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
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
            'toll_fee' => $tollFee,
            'other_fee' => 0,
            'total_fee' => $rentalFee + $driverFee + $tollFee,
            'paid' => false,
        ]);

        $this->notifyAdmins($booking);

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
        $this->notifyRequester($booking);

        return back()->with('success', 'Status booking diperbarui.');
    }

    /** Email ke semua admin aktif; kegagalan kirim hanya dicatat di log. */
    private function notifyAdmins(Booking $booking): void
    {
        $booking->load(['company', 'vehicle']);

        User::where('role', 'ADMIN')->where('status', 'ACTIVE')->pluck('email')->each(function ($email) use ($booking) {
            try {
                Mail::to($email)->send(new BookingCreated($booking));
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email booking baru ke {$email}: ".$e->getMessage());
            }
        });
    }

    /** Email ke pemesan: lewat user_id bila ada (> 0), jika tidak lewat pic_name. Gagal kirim diabaikan. */
    private function notifyRequester(Booking $booking): void
    {
        $booking->load(['company', 'vehicle']);

        if ($booking->user_id > 0) {
            $recipients = User::where('id', $booking->user_id)->get();
        } else {
            $pics = User::where('role', 'PIC')->where('status', 'ACTIVE')->where('company_id', $booking->company_id)->get();
            $recipients = $pics->where('name', $booking->pic_name);
            $recipients = $recipients->isNotEmpty() ? $recipients : $pics;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new BookingStatusUpdated($booking, $recipient->name));
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email status booking ke {$recipient->email}: ".$e->getMessage());
            }
        }
    }

    private function authorizeView(Booking $booking): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $booking->company_id !== $user->company_id) {
            abort(403);
        }
    }
}
