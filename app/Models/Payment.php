<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class);
    }

    public function tripBid()
    {
        return $this->belongsTo(TripBid::class);
    }
}
