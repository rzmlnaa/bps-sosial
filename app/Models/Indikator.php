<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    protected $fillable = ['kode', 'nama', 'kelompok', 'is_active', 'user_id_add', 'user_id_update'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
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
