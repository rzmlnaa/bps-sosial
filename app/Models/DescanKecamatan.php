<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanKecamatan extends Model
{
    protected $table = 'descan_kecamatan';

    protected $fillable = [
        'kabupaten_id',
        'kode_kecamatan',
        'nama_kecamatan',
        'created_by',
        'updated_by',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function desas()
    {
        return $this->hasMany(DescanDesa::class, 'kecamatan_id');
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
        return $this->hasMany(DescanPeserta::class, 'kecamatan_id');
    }
}
