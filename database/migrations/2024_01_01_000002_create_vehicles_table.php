<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ARMADA (vehicles)
 * status: AVAILABLE (siap dibooking) / MAINTENANCE (sedang perawatan)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate')->unique();           // plat nomor, contoh: B 1234 ABC
            $table->string('type');                       // contoh: Truk Engkel
            $table->unsignedTinyInteger('capacity_ton');   // kapasitas maksimum dalam ton
            $table->enum('status', ['AVAILABLE', 'MAINTENANCE'])->default('AVAILABLE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
