<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripBid extends Model
{
    use HasFactory;

    public function driverDetails()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
    public function trip()
    {
        return $this->belongsTo(TripRequest::class, 'trip_id');
    }
}
