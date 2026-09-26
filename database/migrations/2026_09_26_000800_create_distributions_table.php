<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hasil panen yang diberikan/dijual ke tiap kategori penerima.
 * Satu baris per (produksi, kategori) — pengganti ±20 kolom
 * jumlah_berat_dibagikan_*_kg / jumlah_kepala_keluarga_* / jumlah_orang_*.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('recipient_category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->nullable()->comment('satuan = sectors.harvest_unit');
            $table->unsignedInteger('household_count')->nullable()->comment('jumlah KK');
            $table->unsignedInteger('person_count')->nullable()->comment('jumlah orang');
            $table->timestamps();

            $table->unique(['production_id', 'recipient_category_id']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE distributions ADD CONSTRAINT distributions_quantity_check CHECK (quantity >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
