<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiKemiskinan extends Model
{
    protected $table = 'tb_nilai_kemiskinan';

    protected $fillable = [
        'kabupaten_id',
        'variabel_kemiskinan_id',
        'persentil',
        'nilai',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function variabelKemiskinan()
    {
        return $this->belongsTo(VariabelKemiskinan::class, 'variabel_kemiskinan_id');
    }
}
