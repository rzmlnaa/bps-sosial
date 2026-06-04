<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeIndikator extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function nilaiIndikatorMakros()
    {
        return $this->hasMany(NilaiIndikatorMakro::class, 'periode_indikator_id');
    }
}
