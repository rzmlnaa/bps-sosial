<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanPenilaian extends Model
{
    protected $table = 'descan_penilaian';

    protected $fillable = [
        'peserta_id',
        'penilaian_mandiri_desa',
        'penilaian_mandiri_kab',
        'verifikasi_provinsi',
        'catatan',
    ];

    public function peserta()
    {
        return $this->belongsTo(DescanPeserta::class, 'peserta_id');
    }
}
