<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicMenu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'spreadsheet_id',
        'gid',
        'sheet_mode',
        'order_number',
        'is_active',
        'created_by',
    ];

    public function parent()
    {
        return $this->belongsTo(DynamicMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DynamicMenu::class, 'parent_id')->orderBy('order_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
