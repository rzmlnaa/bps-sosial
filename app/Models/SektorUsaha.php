<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SektorUsaha extends Model
{
    protected $fillable = ['kode', 'nama', 'user_id_add', 'user_id_update'];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }
}
