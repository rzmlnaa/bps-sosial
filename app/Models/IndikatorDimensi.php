<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorDimensi extends Model
{
    use HasFactory;

    protected $table = 'indikator_dimensi';
    protected $guarded = [];

    public function indikatorMakro()
    {
        return $this->belongsTo(IndikatorMakro::class, 'indikator_makro_id');
    }

    public function dimensi()
    {
        return $this->belongsTo(Dimensi::class, 'dimensi_id');
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nilaiIndikatorMakros()
    {
        return $this->hasMany(NilaiIndikatorMakro::class, 'indikator_dimensi_id');
    }
}
