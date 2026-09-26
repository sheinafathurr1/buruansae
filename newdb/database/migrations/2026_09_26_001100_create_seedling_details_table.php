<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribut khusus bibit (1:1 dengan productions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedling_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('origin', 100)->nullable()->comment('asal bibit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedling_details');
    }
};
