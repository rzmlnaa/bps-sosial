<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorMakro extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bidang()
    {
        return $this->belongsTo(IndikatorBidang::class, 'indikator_bidang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function indikatorDimensis()
    {
        return $this->hasMany(IndikatorDimensi::class, 'indikator_makro_id');
    }
}
