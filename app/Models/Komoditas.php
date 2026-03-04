<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    protected $table = 'tb_komoditas';

    protected $fillable = [
        'kategori_id',
        'nama_komoditas',
        'satuan',
        'batas_selisih_harga',
        'order_number',
        'user_id_add',
        'user_id_update',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKomoditas::class, 'kategori_id');
    }

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }
}
