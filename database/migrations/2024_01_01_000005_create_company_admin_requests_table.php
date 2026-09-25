<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel PERMINTAAN ADMIN TAMBAHAN (company_admin_requests)
 * Dipakai ketika seorang PIC ingin menambahkan rekan kerja sebagai PIC
 * kedua/ketiga untuk perusahaan yang sama. Admin Fleet yang menyetujui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_admin_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('requested_name');
            $table->string('requested_email');
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamps();

            $table->index('status');
            $table->index('requested_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_admin_requests');
    }
};
