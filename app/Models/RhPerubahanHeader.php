<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhPerubahanHeader extends Model
{
    protected $table = 'tb_rh_perubahan_header';
    protected $fillable = ['rh_tahun_id', 'tanggal_perubahan', 'label', 'user_id_add'];

    public function rhTahun()
    {
        return $this->belongsTo(RhTahun::class, 'rh_tahun_id');
    }

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function details()
    {
        return $this->hasMany(RhPerubahanDetail::class, 'rh_perubahan_header_id');
    }
}
