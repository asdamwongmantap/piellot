<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel PERUSAHAAN (companies)
 * Setiap PIC yang mendaftar akan membuat 1 baris di sini.
 * status: PENDING (menunggu approval admin) -> ACTIVE / REJECTED
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('primary_pic_name');
            $table->string('phone');
            $table->string('email');
            $table->enum('status', ['PENDING', 'ACTIVE', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
