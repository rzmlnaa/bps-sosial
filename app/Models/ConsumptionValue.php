<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumptionValue extends Model
{
    protected $guarded = ['id'];

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function coicop()
    {
        return $this->belongsTo(Coicop::class);
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }
}
