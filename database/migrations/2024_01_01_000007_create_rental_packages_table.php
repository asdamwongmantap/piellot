<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel PAKET DURASI (rental_packages)
 * Menggantikan konstanta Booking::PACKAGES agar bisa diatur Admin Fleet
 * lewat halaman Pengaturan tanpa perlu deploy ulang.
 * code dipakai sebagai nilai booking.package_code (bukan foreign key,
 * supaya booking lama tetap valid walau paket kemudian dihapus/diubah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();     // contoh: 4h, 8h, 24h
            $table->string('label');                  // contoh: "4 Jam"
            $table->string('window_label');            // contoh: "08.00–12.00"
            $table->unsignedBigInteger('rate');       // tarif paket dalam rupiah
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('rental_packages')->insert([
            ['code' => '4h', 'label' => '4 Jam', 'window_label' => '08.00–12.00', 'rate' => 400_000, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '8h', 'label' => '8 Jam', 'window_label' => '08.00–17.00', 'rate' => 650_000, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '24h', 'label' => '24 Jam', 'window_label' => '08.00–08.00 (+1 hari)', 'rate' => 1_200_000, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_packages');
    }
};
