<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalPackage extends Model
{
    protected $fillable = ['code', 'label', 'area', 'window_label', 'start_hour', 'duration_hours', 'rate', 'driver_rate', 'late_fee', 'tolerance_hours', 'sort_order', 'is_active'];

    protected $casts = [
        'start_hour' => 'integer',
        'duration_hours' => 'integer',
        'rate' => 'integer',
        'driver_rate' => 'integer',
        'late_fee' => 'integer',
        'tolerance_hours' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_code', 'code');
    }
}
