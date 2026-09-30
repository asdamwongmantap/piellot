<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalPackage extends Model
{
    protected $fillable = ['code', 'label', 'window_label', 'rate', 'sort_order', 'is_active'];

    protected $casts = [
        'rate' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_code', 'code');
    }
}
