<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripRequest extends Model
{
    use HasFactory;

    public function carDetails()
    {
        return $this->belongsTo(CartType::class, 'cart_type');
    }
    public function userDetails()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function bid()
    {
        return $this->hasMany(TripBid::class, 'trip_id');
    }
    public function bids()
    {
        return $this->hasMany(TripBid::class, 'trip_id');
    }
}
