<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescanJenisOutput extends Model
{
    protected $table = 'descan_jenis_output';

    protected $fillable = [
        'nama_output',
        'is_wajib',
    ];
}
