<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kelurahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 100);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['district_id', 'name']);
        });

        // Laravel belum punya API CHECK; SQLite tidak mendukung ADD CONSTRAINT.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE villages ADD CONSTRAINT villages_coordinates_check CHECK (
                (latitude IS NULL OR latitude BETWEEN -90 AND 90)
                AND (longitude IS NULL OR longitude BETWEEN -180 AND 180))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
