<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alur status booking:
 * PENDING -> APPROVED -> ON_GOING -> COMPLETED -> INVOICING -> PAID, atau PENDING -> REJECTED.
 * Enum diganti string agar status baru mudah ditambah; CONFIRMED lama menjadi APPROVED.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 20)->default('PENDING')->change();
            $table->string('payment_method', 30)->nullable()->after('paid');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });

        DB::table('bookings')->where('status', 'CONFIRMED')->update(['status' => 'APPROVED']);
        DB::table('bookings')->where('status', 'COMPLETED')->where('paid', true)->update(['status' => 'PAID']);
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', 'PAID')->update(['status' => 'COMPLETED']);
        DB::table('bookings')->whereIn('status', ['APPROVED', 'ON_GOING'])->update(['status' => 'CONFIRMED']);
        DB::table('bookings')->where('status', 'INVOICING')->update(['status' => 'COMPLETED']);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paid_at']);
            $table->enum('status', ['PENDING', 'CONFIRMED', 'REJECTED', 'COMPLETED'])->default('PENDING')->change();
        });
    }
};
