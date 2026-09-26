<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bibit extends Model
{
    use HasFactory;
    protected $table = 'data_bibit';
    protected $fillable = [
        'id_bibit',
        'id_kelompok',
        'tanggal_tanam',
        'nama_sayur',
        'asal_bibit',
        'keterangan',
        'jumlah_semai',
        'prakiraan_jumlah_panen',
        'waktu_prakiraan_panen',
        'waktu_panen',
        'jumlah_kp',
        'jumlah_ms',
        'jumlah_sekolah',
        'jumlah_pkk',
        'jumlah_posyandu',
        'jumlah_lainnya',
        'jumlah_kk',
        'jumlah_orang',
        'jumlah_dijual_pohon',
        'jumlah_dijual_orang',
        'jumlah_dijual_orang',
        'jumlah_dijual_kk',
        'harga_jual',
        'gambar',
    ];
}
