<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanPeserta extends Model
{
    protected $table = 'descan_peserta';

    protected $fillable = [
        'desa_id',
        'kecamatan_id',
        'kabupaten_id',
        'periode_id',
        'created_by',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function periode()
    {
        return $this->belongsTo(DescanPeriode::class, 'periode_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progresses()
    {
        return $this->hasMany(DescanProgressDesa::class, 'peserta_id');
    }

    public function outputs()
    {
        return $this->hasMany(DescanOutputDesa::class, 'peserta_id');
    }

    public function buktiDukungs()
    {
        return $this->hasMany(DescanBuktiDukungDesa::class, 'peserta_id');
    }

    public function penilaian()
    {
        return $this->hasOne(DescanPenilaian::class, 'peserta_id');
    }
}
