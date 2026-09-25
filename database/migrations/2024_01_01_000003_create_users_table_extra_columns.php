<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom khusus PIELLOT ke tabel `users` bawaan Laravel.
 * role   : ADMIN (Admin Fleet, mengelola semua data) / PIC (mewakili 1 perusahaan)
 * status : ACTIVE / PENDING (menunggu approval) / REJECTED
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ADMIN', 'PIC'])->default('PIC')->after('email');
            $table->foreignId('company_id')->nullable()->after('role')
                ->constrained('companies')->nullOnDelete();
            $table->enum('status', ['ACTIVE', 'PENDING', 'REJECTED'])->default('PENDING')->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['role', 'status']);
        });
    }
};
