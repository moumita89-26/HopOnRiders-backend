<?php

namespace Tests\Unit;

use App\Services\CustomerCancellationPolicy;
use PHPUnit\Framework\TestCase;

class CustomerCancellationPolicyTest extends TestCase
{
    public function test_policy_refunds_use_total_booking_fare_without_multiplying_seats_again(): void
    {
        $policy = new CustomerCancellationPolicy;
        foreach ([1 => 12000, 2 => 12000, 3 => 10000, 4 => 5000, 5 => 12000, 6 => 12000, 7 => 0] as $type => $expected) {
            $result = $policy->calculate((object) [
                'total_fare' => '120.00', 'booking_fee' => '20.00', 'status' => 4,
                'cancel_type' => $type, 'seat_price' => 25, 'seats_booked' => 4,
            ]);
            $this->assertSame($expected, $result['entitlement_cents'], 'Cancellation type '.$type);
            $this->assertFalse($result['review']);
        }
    }

    public function test_driver_cancelling_after_late_cancellation_refunds_remaining_fare_but_not_fee(): void
    {
        $policy = new CustomerCancellationPolicy;
        foreach ([['cancel_type' => 5], ['cancel_type' => 4, 'remaining_refund_paid' => 1]] as $case) {
            $result = $policy->calculate((object) ($case + [
                'total_fare' => 120, 'booking_fee' => 20, 'status' => 4, 'is_late_cancellation' => 1,
            ]));
            $this->assertSame(10000, $result['entitlement_cents']);
        }
    }

    public function test_non_cancelled_and_no_show_bookings_do_not_accrue_credit(): void
    {
        foreach ([1, 2, 3, 5, 7] as $status) {
            $result = (new CustomerCancellationPolicy)->calculate((object) [
                'total_fare' => 120, 'booking_fee' => 20, 'status' => $status, 'cancel_type' => 5,
            ]);
            $this->assertSame(0, $result['entitlement_cents']);
        }
    }

    public function test_missing_reason_or_invalid_amount_requires_review(): void
    {
        foreach ([['cancel_type' => 0], ['booking_fee' => 130]] as $case) {
            $result = (new CustomerCancellationPolicy)->calculate((object) ($case + [
                'total_fare' => 120, 'booking_fee' => 20, 'status' => 4, 'cancel_type' => 5,
            ]));
            $this->assertSame(0, $result['entitlement_cents']);
            $this->assertTrue($result['review']);
        }
    }
}
