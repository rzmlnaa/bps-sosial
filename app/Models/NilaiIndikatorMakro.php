<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiIndikatorMakro extends Model
{
    use HasFactory;

    protected $table = 'nilai_indikator_makros';
    protected $guarded = [];

    public function periode()
    {
        return $this->belongsTo(PeriodeIndikator::class, 'periode_indikator_id');
    }

    public function indikatorDimensi()
    {
        return $this->belongsTo(IndikatorDimensi::class, 'indikator_dimensi_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
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
