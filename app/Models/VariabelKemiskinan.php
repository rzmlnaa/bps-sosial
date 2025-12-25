<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariabelKemiskinan extends Model
{
    protected $table = 'tb_variabel_kemiskinan';

    protected $fillable = [
        'nama_variabel',
        'bulan',
        'tahun',
        'user_id_add',
    ];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }
}
