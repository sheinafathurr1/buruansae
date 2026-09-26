<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vegetables extends Model
{
    use HasFactory;
    protected $table = 'data_sayur';
    protected $fillable = [
        'id_sayur',
        'id_kelompok',
        'nama_sayur',
        'tanggal_tanam',
        'kategori_tumbuhan',
        'jumlah_tanam',
        'prakiraan_jumlah_panen',
        'waktu_prakiraan_panen',
        'waktu_panen',
        'jumlah_panen',
        'jumlah_berat_kp_kg',
        'jumlah_kepala_keluarga_kp_kk',
        'jumlah_orang_kp',
        'jumlah_berat_dibagikan_stunting_kg',
        'jumlah_kepala_keluarga_dibagikan_stunting',
        'jumlah_orang_dibagikan_stunting',
        'jumlah_berat_dibagikan_mm_kg',
        'jumlah_kepala_keluarga_dibagikan_mm',
        'jumlah_orang_dibagikan_mm',
        'jumlah_berat_dibagikan_lansia_kg',
        'jumlah_kepala_keluarga_dibagikan_lansia',
        'jumlah_orang_dibagikan_lansia',
        'jumlah_berat_dibagikan_posyandu_kg',
        'jumlah_kepala_keluarga_dibagikan_posyandu',
        'jumlah_orang_dibagikan_posyandu',
        'jumlah_berat_dijual_kg',
        'jumlah_orang_dijual',
        'harga_jual',
        'gambar',

    ];
}
