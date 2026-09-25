<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Menggantikan action create_vehicle / toggle_vehicle / delete_vehicle
 * pada app/api/action/route.ts. Semua rute di sini dibatasi middleware
 * `admin` (lihat routes/web.php).
 */
class VehicleController extends Controller
{
    public function index()
    {
        return Inertia::render('Vehicles/Index', [
            'vehicles' => Vehicle::orderBy('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate' => ['required', 'string', 'min:4', 'max:20'],
            'type' => ['required', 'string', 'min:3', 'max:60'],
            'capacity_ton' => ['required', 'integer', 'min:1', 'max:30'],
        ], [], [
            'plate' => 'plat nomor',
            'type' => 'tipe armada',
            'capacity_ton' => 'kapasitas',
        ]);

        $plate = strtoupper($validated['plate']);

        if (Vehicle::whereRaw('lower(plate) = ?', [strtolower($plate)])->exists()) {
            return back()->withErrors(['plate' => 'Plat nomor tersebut sudah terdaftar.'])->withInput();
        }

        Vehicle::create([
            'plate' => $plate,
            'type' => $validated['type'],
            'capacity_ton' => $validated['capacity_ton'],
            'status' => 'AVAILABLE',
        ]);

        return back()->with('success', 'Armada berhasil ditambahkan.');
    }

    public function toggle(Vehicle $vehicle)
    {
        $vehicle->update([
            'status' => $vehicle->status === 'AVAILABLE' ? 'MAINTENANCE' : 'AVAILABLE',
        ]);

        return back()->with(
            'success',
            $vehicle->status === 'MAINTENANCE' ? 'Armada masuk perawatan.' : 'Armada kembali tersedia.'
        );
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->exists()) {
            return back()->with('error', 'Armada memiliki riwayat booking dan belum dapat dihapus.');
        }

        $vehicle->delete();

        return back()->with('success', 'Armada dihapus.');
    }
}
