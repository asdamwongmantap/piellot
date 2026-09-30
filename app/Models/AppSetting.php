<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['driver_rate'];

    protected $casts = [
        'driver_rate' => 'integer',
    ];

    public static function current(): self
    {
        return self::findOrFail(1);
    }
}
