<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sampah extends Model
{
    use HasFactory;
    protected $table = 'data_sampah';
    protected $fillable = [
        'id_data_sampah',
        'id_kelompok',
        'tanggal_masuk',
        'jenis_pengolahan',
        'jumlah_sampah',
        'harga_jual',
        'waktu_prakiraan_panen',
        'prakiraan_jumlah_panen',
        'waktu_panen',
        'jumlah_panen',
        'jumlah_kp',
        'jumlah_ms',
        'jumlah_sekolah',
        'jumlah_pkk',
        'jumlah_posyandu',
        'jumlah_lainnya',
        'jumlah_kk',
        'jumlah_orang',
        'jumlah_dijual_kg',
        'jumlah_dijual_orang',
        'jumlah_dijual_kk',
        'gambar',
    ];
}
