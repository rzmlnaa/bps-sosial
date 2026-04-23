<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanKuota extends Model
{
    protected $table = 'descan_kuota';

    protected $fillable = [
        'periode_id',
        'max_kecamatan',
        'max_desa',
        'created_by',
        'updated_by',
    ];

    public function periode()
    {
        return $this->belongsTo(DescanPeriode::class, 'periode_id');
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
