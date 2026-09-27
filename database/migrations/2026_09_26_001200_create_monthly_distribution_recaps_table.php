<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rekap distribusi bulanan per kelurahan (pengganti data_distribusi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_distribution_recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('period_date');
            $table->decimal('harvest', 12, 3);
            $table->decimal('self_consumption', 12, 3);
            $table->decimal('distributed', 12, 3);
            $table->decimal('sold', 12, 3);
            $table->timestamps();

            $table->unique(['village_id', 'period_date']);
            $table->index('period_date');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE monthly_distribution_recaps ADD CONSTRAINT monthly_distribution_recaps_values_check CHECK (
                harvest >= 0 AND self_consumption >= 0 AND distributed >= 0 AND sold >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_distribution_recaps');
    }
};
