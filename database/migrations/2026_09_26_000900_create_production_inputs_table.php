<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pemupukan (buah) dan pemberian pakan (ikan, ternak).
 * 1:N — bisa dicatat berkali-kali per siklus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('type', ['fertilizer', 'feed'])->comment('pupuk / pakan');
            $table->string('name', 150)->nullable()->comment('mis. jenis pupuk');
            $table->decimal('quantity', 12, 2)->nullable();
            $table->date('applied_date')->nullable();
            $table->timestamps();

            $table->index(['production_id', 'type', 'applied_date']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE production_inputs ADD CONSTRAINT production_inputs_quantity_check CHECK (quantity >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_inputs');
    }
};
