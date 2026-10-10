<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Denda keterlambatan otomatis.
 * - rental_packages: jam mulai & durasi (jam) untuk menghitung batas waktu kembali.
 * - bookings: waktu kembali aktual, nominal denda, dan penanda denda diubah manual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_hour')->default(8)->after('window_label');
            $table->unsignedSmallInteger('duration_hours')->default(4)->after('start_hour');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('returned_at')->nullable()->after('booking_date');
            $table->unsignedBigInteger('late_fee')->default(0)->after('toll_fee');
            $table->boolean('late_fee_adjusted')->default(false)->after('late_fee');
        });

        DB::table('rental_packages')->where('code', '4h')->update(['start_hour' => 8, 'duration_hours' => 4]);
        DB::table('rental_packages')->where('code', '8h')->update(['start_hour' => 8, 'duration_hours' => 9]); // 08.00–17.00
        DB::table('rental_packages')->where('code', '24h-am')->update(['start_hour' => 8, 'duration_hours' => 24]);
        DB::table('rental_packages')->where('code', '24h-pm')->update(['start_hour' => 20, 'duration_hours' => 24]);
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['returned_at', 'late_fee', 'late_fee_adjusted']));
        Schema::table('rental_packages', fn (Blueprint $table) => $table->dropColumn(['start_hour', 'duration_hours']));
    }
};
