<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    public function wayPoint()
    {
        return $this->hasMany(WayPoint::class, 'trip_id');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class, 'trip_id');
    }

    public function driverDetails()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'trip_id');
    }
}
