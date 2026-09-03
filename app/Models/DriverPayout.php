<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayout extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_settlement';

    public const STATUS_SETTLED = 'settled';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'passenger_fare' => 'decimal:2',
            'hopon_fee' => 'decimal:2',
            'driver_payable' => 'decimal:2',
            'completion_verified_at' => 'datetime',
            'settlement_date' => 'date',
            'settled_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class);
    }

    public function tripBid()
    {
        return $this->belongsTo(TripBid::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function settledBy()
    {
        return $this->belongsTo(AdminUser::class, 'settled_by');
    }
}
