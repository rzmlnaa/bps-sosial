<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanBuktiKegiatan extends Model
{
    protected $table = 'descan_bukti_kegiatan';

    protected $fillable = [
        'progress_desa_id',
        'jenis_bukti_id',
        'link_file',
        'created_by',
    ];

    public function progress()
    {
        return $this->belongsTo(DescanProgressDesa::class, 'progress_desa_id');
    }

    public function jenisBukti()
    {
        return $this->belongsTo(DescanJenisBuktiKegiatan::class, 'jenis_bukti_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
