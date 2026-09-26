<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu siklus produksi (tanam → panen) untuk SEMUA sektor.
 * Menggantikan data_sayur, data_buah, data_tanaman_obat, data_ikan,
 * data_ternak, data_olahan_hasil, data_bibit, dan data_sampah.
 * Sektor tidak disimpan di sini; diturunkan dari commodity → sector.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_group_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('commodity_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('planting_category', ['seed', 'seedling', 'tree'])->nullable()
                ->comment('benih / bibit / pohon');
            $table->date('start_date')->nullable()->comment('tanggal tanam / tebar / produksi / masuk');
            $table->decimal('initial_quantity', 12, 2)->nullable()
                ->comment('jumlah tanam / ikan / ternak / semai / sampah');
            $table->date('estimated_harvest_date')->nullable();
            $table->decimal('estimated_harvest_quantity', 12, 3)->nullable();
            $table->date('harvest_date')->nullable();
            $table->decimal('harvest_quantity', 12, 3)->nullable()->comment('satuan = sectors.harvest_unit');
            $table->decimal('harvest_head_count', 12, 2)->nullable()->comment('jumlah ekor (ikan/ternak)');
            $table->unsignedBigInteger('selling_price')->nullable();
            $table->string('notes')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index(['farmer_group_id', 'harvest_date']);
            $table->index('harvest_date');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE productions ADD CONSTRAINT productions_quantities_check CHECK (
                initial_quantity >= 0 AND estimated_harvest_quantity >= 0
                AND harvest_quantity >= 0 AND harvest_head_count >= 0)');
            // Aktifkan setelah data lama "panen sebelum tanam" dibereskan (laporan 02 bagian E):
            // DB::statement('ALTER TABLE productions ADD CONSTRAINT productions_dates_check
            //     CHECK (harvest_date IS NULL OR start_date IS NULL OR harvest_date >= start_date)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
