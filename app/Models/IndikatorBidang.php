<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorBidang extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function indikatorMakros()
    {
        return $this->hasMany(IndikatorMakro::class, 'indikator_bidang_id');
    }
}

