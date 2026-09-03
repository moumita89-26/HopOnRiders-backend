<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public function userData()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function trip()
    {
        return $this->belongsTo(Ride::class, 'trip_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driverPayout()
    {
        return $this->hasOne(DriverPayout::class);
    }
}
