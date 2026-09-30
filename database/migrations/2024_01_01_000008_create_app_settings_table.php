<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel APP_SETTINGS (app_settings)
 * Selalu satu baris (id=1) berisi konfigurasi umum aplikasi.
 * Diawali dengan driver_rate, menggantikan Booking::DRIVER_RATE agar
 * bisa diubah Admin Fleet lewat halaman Pengaturan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_rate')->default(200_000); // tarif tambahan sewa driver
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            'id' => 1,
            'driver_rate' => 200_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
