<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanJenisBuktiKegiatan extends Model
{
    protected $table = 'descan_jenis_bukti_kegiatan';

    protected $fillable = [
        'nama_bukti',
    ];
}
