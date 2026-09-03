<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverSettlement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'settlement_date' => 'date',
        ];
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(AdminUser::class, 'paid_by');
    }

    public function allocations()
    {
        return $this->hasMany(DriverSettlementAllocation::class);
    }
}
