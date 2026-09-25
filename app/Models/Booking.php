<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * Daftar paket sewa beserta label, jam operasional, dan tarif.
     * Ini adalah terjemahan langsung dari `PACKAGES` pada source aslinya
     * (app/piellot-app.tsx & app/api/action/route.ts).
     */
    public const PACKAGES = [
        '4h' => ['label' => '4 Jam', 'window' => '08.00–12.00', 'rate' => 400_000],
        '8h' => ['label' => '8 Jam', 'window' => '08.00–17.00', 'rate' => 650_000],
        '24h' => ['label' => '24 Jam', 'window' => '08.00–08.00 (+1 hari)', 'rate' => 1_200_000],
    ];

    public const DRIVER_RATE = 200_000;

    protected $fillable = [
        'company_id', 'pic_name', 'vehicle_id', 'package_code', 'booking_date',
        'load_ton', 'destination', 'passengers', 'need_driver', 'status',
        'rental_fee', 'driver_fee', 'other_fee', 'total_fee', 'paid',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'need_driver' => 'boolean',
        'paid' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Menghitung ulang total_fee dari komponen rental + driver + biaya lain. */
    public function recalculateTotal(): void
    {
        $this->total_fee = $this->rental_fee + $this->driver_fee + $this->other_fee;
    }

    public function packageLabel(): string
    {
        return self::PACKAGES[$this->package_code]['label'] ?? $this->package_code;
    }
}
