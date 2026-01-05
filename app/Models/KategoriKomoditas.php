<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKomoditas extends Model
{
    protected $table = 'tb_kategori_komoditas';

    protected $fillable = [
        'nama_kategori',
        'user_id_add',
        'user_id_update',
    ];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }

    public function komoditas()
    {
        return $this->hasMany(Komoditas::class, 'kategori_id');
    }
}
