<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Komoditas (KANGKUNG, LELE, AYAM PETELUR, ...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->unsignedSmallInteger('growing_days')->nullable()->comment('durasi tanam (hari)');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unique(['sector_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};
