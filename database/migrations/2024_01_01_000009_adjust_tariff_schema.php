<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penyesuaian ketentuan layanan & tarif:
 * - rental_packages: tarif driver, denda keterlambatan, dan toleransi per paket.
 *   Paket 24 jam dipecah menjadi dua opsi start (08.00 atau 20.00).
 * - bookings.toll_fee: estimasi biaya tol, ditagihkan cost to cost.
 * BBM ditanggung Penyewa langsung di lapangan sehingga tidak dicatat sebagai tagihan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_rate')->default(0)->after('rate');
            $table->unsignedBigInteger('late_fee')->default(50_000)->after('driver_rate');   // denda per jam
            $table->unsignedTinyInteger('tolerance_hours')->default(0)->after('late_fee');   // toleransi tanpa denda
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('toll_fee')->default(0)->after('driver_fee');
        });

        $now = now();

        DB::table('rental_packages')->where('code', '4h')->update([
            'rate' => 200_000, 'driver_rate' => 75_000, 'late_fee' => 50_000, 'tolerance_hours' => 0,
        ]);
        DB::table('rental_packages')->where('code', '8h')->update([
            'rate' => 350_000, 'driver_rate' => 150_000, 'late_fee' => 50_000, 'tolerance_hours' => 0,
        ]);
        DB::table('rental_packages')->where('code', '24h')->update([
            'code' => '24h-am', 'label' => '24 Jam (Luar Kota)', 'window_label' => '08.00–08.00 (+1 hari)',
            'rate' => 500_000, 'driver_rate' => 300_000, 'late_fee' => 50_000, 'tolerance_hours' => 1,
        ]);
        DB::table('bookings')->where('package_code', '24h')->update(['package_code' => '24h-am']);

        DB::table('rental_packages')->insert([
            'code' => '24h-pm', 'label' => '24 Jam (Luar Kota)', 'window_label' => '20.00–20.00 (+1 hari)',
            'rate' => 500_000, 'driver_rate' => 300_000, 'late_fee' => 50_000, 'tolerance_hours' => 1,
            'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('rental_packages')->where('code', '24h-pm')->delete();
        DB::table('bookings')->where('package_code', '24h-pm')->update(['package_code' => '24h-am']);
        DB::table('bookings')->where('package_code', '24h-am')->update(['package_code' => '24h']);
        DB::table('rental_packages')->where('code', '24h-am')->update(['code' => '24h', 'label' => '24 Jam', 'window_label' => '08.00–08.00 (+1 hari)']);

        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('toll_fee'));
        Schema::table('rental_packages', fn (Blueprint $table) => $table->dropColumn(['driver_rate', 'late_fee', 'tolerance_hours']));
    }
};
