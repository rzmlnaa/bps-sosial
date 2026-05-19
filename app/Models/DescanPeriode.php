<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanPeriode extends Model
{
    protected $table = 'descan_periode';

    protected $fillable = [
        'tahun',
        'is_active',
    ];

    public function pesertas()
    {
        return $this->hasMany(DescanPeserta::class, 'periode_id');
    }
}
