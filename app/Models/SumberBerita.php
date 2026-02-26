<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberBerita extends Model
{
    use HasFactory;
    protected $fillable = ['nama', 'is_online', 'user_id_add', 'user_id_update'];

    protected $casts = [
        'is_online' => 'boolean',
    ];

    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_id_add');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_id_update');
    }
}
