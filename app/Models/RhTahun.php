<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhTahun extends Model
{
    protected $table = 'tb_rh_tahun';
    protected $fillable = ['tahun', 'is_active', 'batas_selisih_harga', 'user_id_add'];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function perubahanHeaders()
    {
        return $this->hasMany(RhPerubahanHeader::class, 'rh_tahun_id');
    }
}
