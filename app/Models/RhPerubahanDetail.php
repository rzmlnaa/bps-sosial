<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhPerubahanDetail extends Model
{
    protected $table = 'tb_rh_perubahan_detail';
    protected $fillable = [
        'rh_perubahan_header_id',
        'kabupaten_id',
        'komoditas_id',
        'min_edit',
        'max_edit',
        'alasan',
        'user_id_add'
    ];

    public function revisionHeader()
    {
        return $this->belongsTo(RhPerubahanHeader::class, 'rh_perubahan_header_id');
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
