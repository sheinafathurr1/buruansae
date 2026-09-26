<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori penerima hasil (KP, STUNTING, LANSIA, POSYANDU, DIJUAL, ...).
 * Kategori baru = tambah baris, bukan tambah kolom.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipient_categories');
    }
};
