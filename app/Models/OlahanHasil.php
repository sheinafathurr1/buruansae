<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlahanHasil extends Model
{
    use HasFactory;
    protected $table = 'data_olahan_hasil';
    protected $fillable = [
        'id_data_olahan_hasil',
        'id_kelompok',
        'uji_lab',
        'izin_halal',
        'izin_pirt',
        'resep',
        'tanggal_produksi',
        'jenis_olahan',
        'bahan_dasar',
        'merk',
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
