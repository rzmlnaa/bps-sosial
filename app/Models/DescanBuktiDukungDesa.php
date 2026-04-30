<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanBuktiDukungDesa extends Model
{
    protected $table = 'descan_bukti_dukung_desa';

    protected $fillable = [
        'peserta_id',
        'jenis_bukti_id',
        'link_file',
        'status',
        'alasan_penolakan',
        'created_by',
    ];

    public function peserta()
    {
        return $this->belongsTo(DescanPeserta::class, 'peserta_id');
    }

    public function jenisBukti()
    {
        return $this->belongsTo(DescanJenisBuktiDukung::class, 'jenis_bukti_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
