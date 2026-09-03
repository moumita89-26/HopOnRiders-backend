<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function getState()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    function getCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
