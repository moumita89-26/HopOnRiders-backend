<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = 'admin_menus';

    function children()
    {
        return $this->hasMany(Menus::class, 'parent_id', 'id');
    }

    function parent()
    {
        return $this->belongsTo(Menus::class, 'parent_id');
    }
}
