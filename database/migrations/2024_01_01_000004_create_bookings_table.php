<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel BOOKING (bookings)
 * Satu baris = satu pemesanan armada oleh PIC untuk perusahaannya.
 * package_code: 4h / 8h / 24h -> menentukan rental_fee (lihat App\Models\Booking::PACKAGES)
 * status: PENDING -> CONFIRMED -> COMPLETED, atau PENDING -> REJECTED
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('pic_name');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('package_code', 8);            // 4h | 8h | 24h
            $table->date('booking_date');
            $table->decimal('load_ton', 5, 1);             // estimasi muatan dalam ton, contoh 2.5
            $table->string('destination');
            $table->unsignedTinyInteger('passengers');     // jumlah kernet
            $table->boolean('need_driver')->default(false);
            $table->enum('status', ['PENDING', 'CONFIRMED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->unsignedBigInteger('rental_fee')->default(0);
            $table->unsignedBigInteger('driver_fee')->default(0);
            $table->unsignedBigInteger('other_fee')->default(0);
            $table->unsignedBigInteger('total_fee')->default(0);
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'booking_date']);
            $table->index(['vehicle_id', 'booking_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
