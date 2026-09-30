<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

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
        return RentalPackage::where('code', $this->package_code)->value('label') ?? $this->package_code;
    }
}
