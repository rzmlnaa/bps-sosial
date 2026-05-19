<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PraEksporFenomenaSelection extends Model
{
    protected $fillable = [
        'kabupaten_id',
        'fenomena_id',
        'user_id',
        'tahun',
        'bulan',
        'status',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function fenomena()
    {
        return $this->belongsTo(Fenomena::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
