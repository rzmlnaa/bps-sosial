<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhMasterNilai extends Model
{
    protected $table = 'tb_rh_master_nilai';
    protected $fillable = [
        'rh_tahun_id',
        'kabupaten_id',
        'komoditas_id',
        'min_nilai',
        'max_nilai',
        'alasan',
        'user_id_add'
    ];

    public function rhTahun()
    {
        return $this->belongsTo(RhTahun::class, 'rh_tahun_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class, 'komoditas_id');
    }

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }
}
