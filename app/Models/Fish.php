<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fish extends Model
{
    use HasFactory;
    protected $table = 'data_ikan';
    protected $fillable = [
        'id_ikan',
        'id_kelompok',
        'waktu_pakan',
        'jenis_ikan',
        'jumlah_pakan',
        'jumlah_ikan',
        'prakiraan_jumlah_panen',
        'waktu_prakiraan_panen',
        'waktu_panen',
        'jumlah_panen_kg',
        'jumlah_panen_ekor',
        'jumlah_berat_kp_kg',
        'jumlah_orang_kp',
        'jumlah_kepala_keluarga_kp_kk',
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
