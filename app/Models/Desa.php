<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    protected $table = 'tb_desa';

    protected $fillable = [
        'kecamatan_id',
        'kode_desa',
        'nama_desa',
        'created_by',
        'updated_by',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pesertas()
    {
        return $this->hasMany(DescanPeserta::class, 'desa_id');
    }
}
