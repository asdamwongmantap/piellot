<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/** Menggantikan action update_invoice dan mark_paid pada app/api/action/route.ts. */
class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = Booking::with(['company', 'vehicle'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('company_id', $user->company_id))
            ->latest()
            ->get();

        return Inertia::render('Invoices/Index', ['bookings' => $bookings]);
    }

    public function show(Booking $booking)
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $booking->company_id !== $user->company_id) {
            abort(403);
        }

        $booking->load(['company', 'vehicle']);

        return Inertia::render('Invoices/Show', ['booking' => $booking]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'other_fee' => ['required', 'integer', 'min:0', 'max:100000000'],
        ], [], ['other_fee' => 'biaya lain']);

        $booking->other_fee = $validated['other_fee'];
        $booking->recalculateTotal();
        $booking->save();

        return back()->with('success', 'Tagihan diperbarui.');
    }

    public function markPaid(Booking $booking)
    {
        $booking->update(['paid' => true]);

        return back()->with('success', 'Tagihan ditandai lunas.');
    }
}
