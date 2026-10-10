<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Pengelompokan paket: CITY (dalam kota) atau OUTSIDE (luar kota). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_packages', function (Blueprint $table) {
            $table->string('area', 8)->default('CITY')->after('label');
        });

        DB::table('rental_packages')->whereIn('code', ['24h-am', '24h-pm'])->update(['area' => 'OUTSIDE']);
    }

    public function down(): void
    {
        Schema::table('rental_packages', fn (Blueprint $table) => $table->dropColumn('area'));
    }
};
