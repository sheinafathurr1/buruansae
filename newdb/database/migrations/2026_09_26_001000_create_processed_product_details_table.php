<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribut khusus produk olahan (1:1 dengan productions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('base_ingredient')->nullable()->comment('bahan dasar');
            $table->string('brand', 150)->nullable()->comment('merk');
            $table->text('recipe')->nullable();
            $table->string('lab_test')->nullable()->comment('uji lab');
            $table->string('halal_permit')->nullable()->comment('izin halal');
            $table->string('pirt_permit')->nullable()->comment('izin PIRT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_product_details');
    }
};
