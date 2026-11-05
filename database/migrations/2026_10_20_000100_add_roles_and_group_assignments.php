<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Peran akun (admin / staf), penugasan staf ke kelompok, dan jejak siapa yang
 * mencatat data produksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('staff')->after('username')->comment('admin | staff');
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // Sebelum ada peran, semua akun punya akses penuh. Pertahankan.
        DB::table('users')->update(['role' => 'admin']);

        Schema::create('farmer_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farmer_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'farmer_group_id']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('image')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::dropIfExists('farmer_group_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'must_change_password', 'last_login_at']);
        });
    }
};
