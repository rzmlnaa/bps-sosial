<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'tb_kabupaten';

    protected $fillable = [
        'kode_kab',
        'nama_kabupaten',
        'user_id_add',
        'user_id_update',
        'time_add',
        'time_update',
    ];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }

    public function nilaiKemiskinan()
    {
        return $this->hasMany(NilaiKemiskinan::class, 'kabupaten_id');
    }

    public function praEksporSelections()
    {
        return $this->hasMany(PraEksporFenomenaSelection::class, 'kabupaten_id');
    }

    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id');
    }

    public function nilaiIndikatorMakros()
    {
        return $this->hasMany(NilaiIndikatorMakro::class, 'kabupaten_id');
    }

    public function scopeWithoutIndonesia($query)
    {
        return $query->where('kode_kab', '!=', 1);
    }
}

