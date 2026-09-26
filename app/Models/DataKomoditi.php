<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKomoditi extends Model
{
    use HasFactory;
    protected $table = 'data_komoditi';
    protected $fillable = [
        'nama_komoditi',
        'sektor',
        'durasi_tanam',
        'gambar',
    ];
}
