<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DriverPayout;
use App\Models\Payment;
use App\Models\TripBid;
use App\Models\TripRequest;
use Illuminate\Support\Facades\DB;

class DriverPayoutService
{
    private const PAID_PAYMENT_STATUSES = ['completed', 'paid', 'successful', 'success'];

    public function createPendingForCompletedBooking(Booking $booking): ?DriverPayout
    {
        $booking->loadMissing('trip');

        if (! $booking->trip || ! (bool) $booking->is_verify_epin) {
            return null;
        }

        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->whereIn(DB::raw('LOWER(status)'), self::PAID_PAYMENT_STATUSES)
            ->latest('id')
            ->first();

        if (! $payment) {
            return null;
        }

        $fare = (float) ($booking->total_fare ?? 0);
        $hoponFee = (float) ($booking->booking_fee ?? 0);
        $attributes = [
            'ride_id' => $booking->trip_id,
            'driver_id' => $booking->trip->driver_id,
            'passenger_id' => $booking->passenger_id,
            'payment_id' => $payment->id,
            'seats_booked' => max(1, (int) ($booking->seats_booked ?? 1)),
            'passenger_fare' => $fare,
            'hopon_fee' => $hoponFee,
            'driver_payable' => max(0, $fare - $hoponFee),
            'passenger_payment_reference' => $payment->payment_reference ?? null,
            'paygo_transaction_reference' => $payment->paygo_transaction_reference ?? null,
            'customer_payment_status' => 'paid',
            'completion_verified_at' => now(),
            'payout_status' => DriverPayout::STATUS_PENDING,
        ];

        return DB::transaction(function () use ($booking, $attributes) {
            $existing = DriverPayout::where('booking_id', $booking->id)->lockForUpdate()->first();

            if ($existing) {
                if ($existing->payout_status === DriverPayout::STATUS_PENDING) {
                    $existing->update($attributes);
                }

                return $existing->fresh();
            }

            return DriverPayout::create(['booking_id' => $booking->id] + $attributes);
        });
    }

    public function createPendingForCompletedTripRequest(TripRequest $tripRequest): ?DriverPayout
    {
        if (! (bool) $tripRequest->is_verify_epin) {
            return null;
        }

        $tripBid = TripBid::query()
            ->where('trip_id', $tripRequest->id)
            ->where('driver_id', $tripRequest->driver_id)
            ->whereIn('status', [1, 2, 3])
            ->latest('id')
            ->first();

        if (! $tripBid) {
            return null;
        }

        $payment = Payment::query()
            ->where(function ($query) use ($tripRequest, $tripBid) {
                $query->where('trip_request_id', $tripRequest->id)
                    ->orWhere('trip_bid_id', $tripBid->id);
            })
            ->whereIn(DB::raw('LOWER(status)'), self::PAID_PAYMENT_STATUSES)
            ->latest('id')
            ->first();

        if (! $payment) {
            return null;
        }

        $fare = (float) ($tripBid->total_fare ?? $tripBid->proposed_fare ?? $tripBid->seat_price ?? 0);
        $hoponFee = (float) ($tripBid->booking_fee ?? 0);
        $attributes = [
            'booking_id' => null,
            'ride_id' => null,
            'trip_bid_id' => $tripBid->id,
            'driver_id' => $tripRequest->driver_id,
            'passenger_id' => $tripRequest->passenger_id,
            'payment_id' => $payment->id,
            'seats_booked' => max(1, (int) ($tripRequest->seats_required ?? 1)),
            'passenger_fare' => $fare,
            'hopon_fee' => $hoponFee,
            'driver_payable' => max(0, $fare - $hoponFee),
            'passenger_payment_reference' => $payment->payment_reference ?? null,
            'paygo_transaction_reference' => $payment->paygo_transaction_reference ?? null,
            'customer_payment_status' => 'paid',
            'completion_verified_at' => now(),
            'payout_status' => DriverPayout::STATUS_PENDING,
        ];

        return DB::transaction(function () use ($tripRequest, $attributes) {
            $existing = DriverPayout::where('trip_request_id', $tripRequest->id)->lockForUpdate()->first();

            if ($existing) {
                if ($existing->payout_status === DriverPayout::STATUS_PENDING) {
                    $existing->update($attributes);
                }

                return $existing->fresh();
            }

            return DriverPayout::create(['trip_request_id' => $tripRequest->id] + $attributes);
        });
    }
}
