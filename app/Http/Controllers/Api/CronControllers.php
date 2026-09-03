<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ride;
use App\Models\TripBid;
use App\Models\TripRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CronControllers extends Controller
{
    public function autoCancelRide()
    {
        DB::transaction(function () {
            $newTime = Carbon::now()->addMinutes(30);
            $rides = Ride::with(['booking'])
                ->whereIn('status', [1, 2])
                ->where('departure_time', '<=', $newTime)
                ->get();

            foreach ($rides as $ride) {
                foreach ($ride->booking as $booking) {
                    // Process only approved bookings
                    if ($booking->status == 2 || $booking->status == 1 || $booking->status == 0) {
                        $booking->status = 7;          // No Show
                        $booking->cancel_type = 7;     // No Show
                        $booking->refund_seat_amount = 0;
                        $booking->refund_booking_fee_amount = 0;
                        // Driver keeps full fare
                        $booking->driver_compensation = $booking->seat_price;
                        $booking->is_late_cancellation = 0;
                        $booking->late_review_added = 0;
                        $booking->remaining_refund_paid = 0;
                        $booking->save();
                    }
                }
                // Mark Ride Completed
                $ride->ride_status = 3; // Completed
                $ride->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Ride completion cron executed successfully. at ' . Carbon::now()
        ]);
    }

    public function autoCancellTrip()
    {
        DB::transaction(function () {
            $newTime = Carbon::now()->addMinutes(30);
            $trips = TripRequest::with(['bids'])
                ->whereIn('status', [1, 2])
                ->where('requested_date', '<=', $newTime)
                ->get();

            foreach ($trips as $trip) {
                foreach ($trip->bids as $bid) {
                    // Process only approved bookings
                    if ($bid->status == 2 || $bid->status == 1 || $bid->status == 0) {
                        $bid->status = 7;          // No Show
                        $bid->cancel_type = 7;     // No Show
                        $bid->refund_seat_amount = 0;
                        $bid->refund_booking_fee_amount = 0;
                        // Driver keeps full fare
                        $bid->driver_compensation = $bid->seat_price;
                        $bid->is_late_cancellation = 0;
                        $bid->late_review_added = 0;
                        $bid->remaining_refund_paid = 0;
                        $bid->save();
                    }
                }
                // Mark Ride Completed
                $trip->status = 3; // Completed
                $trip->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Trip completion cron executed successfully. at ' . Carbon::now()
        ]);
    }

    public function suspendDriverIfRequired()
    {
        DB::transaction(function () {
            $sixMonthsAgo = Carbon::now()->subMonths(6);
            $rideCancelCount = Booking::where('cancel_type', 5)
                ->where('cancelled_at', '>=', $sixMonthsAgo)
                ->get();
            $tripCancelCount = TripBid::where('cancel_type', 5)
                ->where('cancelled_at', '>=', $sixMonthsAgo)
                ->get();
            if ((count($rideCancelCount) + count($tripCancelCount)) > 0) {
                foreach ($rideCancelCount as $key => $ride) {
                    $driverId = $ride->trip->driver_id;
                    User::where('id', $driverId)->update([
                        'is_suspend' => 0,
                        'suspended_at' => now(),
                        'suspend_reason' => 'Driver cancelled a ride/trip within the last 6 months.'
                    ]);
                }
                foreach ($rideCancelCount as $key => $bids) {
                    $driverId = $bids->driver_id;
                    User::where('id', $driverId)->update([
                        'is_suspend' => 0,
                        'suspended_at' => now(),
                        'suspend_reason' => 'Driver cancelled a ride/trip within the last 6 months.'
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Driver cancelled a ride/trip within the last 6 months. suspend Driver account at  ' . Carbon::now()
        ]);
    }
}
