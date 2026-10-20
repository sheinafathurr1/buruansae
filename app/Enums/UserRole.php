<?php

namespace App\Enums;

/** Peran akun dashboard pengelola. */
enum UserRole: string
{
    /** Akses penuh: semua kelompok, data master, dan akun pengguna. */
    case Admin = 'admin';

    /** Pegawai/penyuluh: hanya kelompok yang ditugaskan kepadanya. */
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Staff => 'Staf',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Mengelola semua kelompok, komoditas, dan akun pengguna.',
            self::Staff => 'Hanya mengisi dan mengubah data kelompok yang ditugaskan kepadanya.',
        };
    }
}
