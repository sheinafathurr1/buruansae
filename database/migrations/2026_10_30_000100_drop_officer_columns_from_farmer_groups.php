<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyuluh & pendamping tidak lagi diketik sebagai teks bebas di data
 * kelompok. Yang bertanggung jawab atas kelompok adalah petugas (akun staf)
 * yang ditugaskan lewat farmer_group_user.
 *
 * Nama penyuluh/pendamping lama tetap ada di database aplikasi lama
 * (data_kelompok.penyuluh / pendamping). down() hanya mengembalikan kolomnya,
 * bukan isinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_groups', function (Blueprint $table) {
            $table->dropColumn(['extension_officer', 'facilitator']);
        });
    }

    public function down(): void
    {
        Schema::table('farmer_groups', function (Blueprint $table) {
            $table->string('extension_officer', 150)->nullable()->after('phone')->comment('penyuluh');
            $table->string('facilitator', 150)->nullable()->after('extension_officer')->comment('pendamping');
        });
    }
};
