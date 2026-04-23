<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanJenisBuktiDukung extends Model
{
    protected $table = 'descan_jenis_bukti_dukung';
    public $timestamps = false;

    protected $fillable = [
        'nama_bukti',
        'is_wajib',
    ];
}
