<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanOutputDesa extends Model
{
    protected $table = 'descan_output_desa';

    protected $fillable = [
        'peserta_id',
        'jenis_output_id',
        'link',
        'status',
        'created_by',
        'updated_by',
    ];

    public function peserta()
    {
        return $this->belongsTo(DescanPeserta::class, 'peserta_id');
    }

    public function jenisOutput()
    {
        return $this->belongsTo(DescanJenisOutput::class, 'jenis_output_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
