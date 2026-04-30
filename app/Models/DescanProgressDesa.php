<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanProgressDesa extends Model
{
    protected $table = 'descan_progress_desa';

    protected $fillable = [
        'peserta_id',
        'kegiatan_id',
        'target_tanggal',
        'realisasi_tanggal',
        'status',
        'alasan_penolakan',
        'created_by',
        'updated_by',
        'verified_by',
        'verified_at',
    ];

    public function peserta()
    {
        return $this->belongsTo(DescanPeserta::class, 'peserta_id');
    }

    public function kegiatan()
    {
        return $this->belongsTo(DescanKegiatan::class, 'kegiatan_id');
    }

    public function buktis()
    {
        return $this->hasMany(DescanBuktiKegiatan::class, 'progress_desa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
