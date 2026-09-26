<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kelompok (kelompok tani / Buruan SAE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('rw')->nullable();
            $table->string('name', 150)->index();
            $table->string('leader_name', 150)->nullable()->comment('nama ketua');
            $table->string('phone', 20)->nullable();
            $table->string('extension_officer', 150)->nullable()->comment('penyuluh');
            $table->string('facilitator', 150)->nullable()->comment('pendamping');
            $table->decimal('land_area_m2', 10, 2)->nullable();
            $table->string('land_status', 50)->nullable();
            $table->boolean('is_active')->nullable()->comment('NULL = belum diketahui');
            $table->string('status_note')->nullable();
            $table->string('land_photo')->nullable();
            $table->string('leader_photo')->nullable();
            $table->string('description_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'rw']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE farmer_groups ADD CONSTRAINT farmer_groups_values_check CHECK (
                (rw IS NULL OR rw >= 1) AND (land_area_m2 IS NULL OR land_area_m2 >= 0))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_groups');
    }
};
