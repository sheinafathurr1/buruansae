<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKelompok extends Model
{
    use HasFactory;
    protected $table = 'data_kelompok';
    protected $fillable = [
        'id_kelompok',
        'penyuluh',
        'pendamping',
        'kecamatan',
        'kelurahan',
        'rw',
        'nama_kelompok',
        'nama_ketua',
        'luas_lahan',
        'status_lahan',
        'status_keaktifan',
        'keterangan_status',
    ];
}
