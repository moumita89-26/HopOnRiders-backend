<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    public function userDetails()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function DriverDetails()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
