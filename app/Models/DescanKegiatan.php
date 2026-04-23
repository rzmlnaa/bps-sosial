<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanKegiatan extends Model
{
    protected $table = 'descan_kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'urutan',
    ];
}
