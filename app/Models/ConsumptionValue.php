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
}
