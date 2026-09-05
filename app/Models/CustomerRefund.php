<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRefund extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'refund_date' => 'date'];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(AdminUser::class, 'paid_by');
    }

    public function allocations()
    {
        return $this->hasMany(CustomerRefundAllocation::class);
    }
}
