<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coicop extends Model
{
    protected $table = 'coicops';
    protected $guarded = ['id'];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }
}
