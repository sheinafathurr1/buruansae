<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat pencatatan data tanam & panen per akun, untuk memantau kinerja
 * petugas. Nama komoditas & keterangan disalin saat dicatat agar riwayat
 * tetap terbaca walau datanya kemudian dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30)->comment('App\Enums\ActivityAction');
            $table->string('sector', 30)->nullable()->comment('App\Enums\SectorType');
            $table->foreignId('production_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('farmer_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commodity_name', 150)->nullable();
            $table->string('detail', 150)->nullable()->comment('mis. jumlah tanam / panen');
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
