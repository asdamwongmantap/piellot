<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'pic_name', 'vehicle_id', 'package_code', 'booking_date', 'returned_at',
        'load_ton', 'destination', 'passengers', 'need_driver', 'status',
        'rental_fee', 'driver_fee', 'toll_fee', 'late_fee', 'late_fee_adjusted', 'other_fee', 'total_fee', 'paid',
    ];

    protected $appends = ['package_label', 'package_window', 'due_at'];

    protected $casts = [
        'booking_date' => 'date',
        'returned_at' => 'datetime:Y-m-d\TH:i',
        'late_fee_adjusted' => 'boolean',
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

    /** Menghitung ulang total_fee dari komponen rental + driver + tol + biaya lain. */
    public function recalculateTotal(): void
    {
        $this->total_fee = $this->rental_fee + $this->driver_fee + $this->toll_fee + $this->late_fee + $this->other_fee;
    }

    public function packageLabel(): string
    {
        return $this->package()?->label ?? $this->package_code;
    }

    public function getPackageLabelAttribute(): string
    {
        return $this->packageLabel();
    }

    public function getPackageWindowAttribute(): ?string
    {
        return $this->package()?->window_label;
    }

    /** Batas waktu kembali: tanggal booking + jam mulai paket + durasi paket. */
    public function dueAt(): ?Carbon
    {
        $package = $this->package();

        if (! $package || $package->start_hour === null || $package->duration_hours === null) {
            return null;
        }

        return $this->booking_date->copy()->setTime($package->start_hour, 0)->addHours($package->duration_hours);
    }

    public function getDueAtAttribute(): ?string
    {
        return $this->dueAt()?->format('Y-m-d\TH:i');
    }

    /**
     * Denda otomatis: keterlambatan dikurangi toleransi paket, dibulatkan ke atas per jam.
     * Selama masih dalam toleransi (atau belum ada waktu kembali) denda = 0.
     */
    public function calculateLateFee(): int
    {
        $package = $this->package();
        $due = $this->dueAt();

        if (! $package || ! $due || ! $this->returned_at) {
            return 0;
        }

        $lateMinutes = $due->diffInMinutes($this->returned_at, false) - $package->tolerance_hours * 60;

        return $lateMinutes > 0 ? (int) ceil($lateMinutes / 60) * $package->late_fee : 0;
    }

    /** Paket di-cache per request agar daftar booking tidak memicu N+1 query. */
    private function package(): ?RentalPackage
    {
        static $packages;
        $packages ??= RentalPackage::all()->keyBy('code');

        return $packages[$this->package_code] ?? null;
    }
}
