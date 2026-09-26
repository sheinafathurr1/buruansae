<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Semua pengujian memakai data acuan (sektor & kategori penerima). Harus
     * seragam: Laravel memakai ulang database SQLite in-memory yang sudah
     * dimigrasi antar kelas pengujian, jadi kelas tanpa seed akan membuat
     * kelas berikutnya kehilangan data acuan.
     */
    protected $seed = true;
}
