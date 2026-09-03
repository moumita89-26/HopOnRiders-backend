<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Ride;
use App\Models\TripBid;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class TripBidController extends Controller
{
    public function bidTrip(Request $request)
    {
        $checkUser = CustomHelper::CheckUserExits();
        if ($checkUser->is_suspend) {
            return CustomHelper::ErrorResponse("Your Account has been suspended. Please contact admin");
        }
        try {
            $bidId =  TripBid::where('trip_id', $request->tripId)->where('driver_id', $request->userId)->first();
            if ($bidId) {
                TripBid::where('trip_id', $request->tripId)->where('driver_id', $request->userId)->update([
                    'proposed_fare' => $request->proposedFare,
                ]);

                $userId = TripRequest::where('id', $request->tripId)->first();
                $deviceToken = User::where('id', $userId->passenger_id)->first();
                $userData = CustomHelper::CheckUserExits();
                $message = 'Ride Bid submitted by ' . $userData->name;
                if ($deviceToken) {
                    $tokens = [$deviceToken->device_token];
                    CustomHelper::NotifyMultipleUsers($tokens, 'Your Ride Bid', $message, ['tripId' => $request->tripId]);
                }
                Notification::insert([
                    "user_id" => $userId->passenger_id,
                    "booked_user_id" => $userData->id,
                    'is_read' => 0,
                    'bid_id' => $bidId->id,
                    'title' => "Ride Bid",
                    'trip_id' => $request->tripId,
                    'message' => $message,
                    'ride_trip_type' => 2,
                    'type' => 1
                ]);
            } else {
                $bidId = TripBid::insertGetId([
                    'trip_id' => $request->tripId,
                    'driver_id' => $request->userId,
                    'proposed_fare' => $request->proposedFare,
                    'status' => 0,
                ]);
                $userId = TripRequest::where('id', $request->tripId)->first();
                $deviceToken = User::where('id', $userId->passenger_id)->first();
                $userData = CustomHelper::CheckUserExits();
                $message = 'Ride Bid submitted by ' . $userData->name;
                if ($deviceToken) {
                    $tokens = [$deviceToken->device_token];
                    CustomHelper::NotifyMultipleUsers($tokens, 'Your Ride Bid', $message, ['tripId' => $request->tripId]);
                }
                Notification::insert([
                    "user_id" => $userId->passenger_id,
                    "booked_user_id" => $userData->id,
                    'is_read' => 0,
                    'bid_id' => $bidId,
                    'title' => "Ride Bid",
                    'trip_id' => $request->tripId,
                    'message' => $message,
                    'ride_trip_type' => 2,
                    'type' => 1
                ]);
            }
            return CustomHelper::SuccessWithoutData("Price quote sent successfully.");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function bidTripAccept(Request $request)
    {
        $checkUser = CustomHelper::CheckUserExits();
        if ($checkUser->is_suspend) {
            return CustomHelper::ErrorResponse("Your Account has been suspended. Please contact admin");
        }
        try {
            $checkTrip = TripBid::where('trip_id', $request->tripId)->where('status', 0)->first();
            if ($checkTrip) {
                TripBid::where('id', $request->bidId)->update([
                    'status' => 1,
                    'total_fare' => $request->totalFare,
                    'seat_price' => $request->seatPrice,
                    'booking_fee' => $request->bookingFee,
                ]);
                TripRequest::where('id', $request->tripId)->update(['status' => 1, 'driver_id' => $checkTrip->driver_id]);
                $deviceToken = User::where('id', $checkTrip->driver_id)->first();
                $userData = CustomHelper::CheckUserExits();
                $message = "";
                if ($deviceToken) {
                    $message = 'Ride Bid accepted by ' . $userData->name;
                    $tokens = [$deviceToken->device_token];
                    CustomHelper::NotifyMultipleUsers($tokens, 'Your Ride Bid', $message, ['tripId' => $request->tripId]);
                }

                Notification::insert([
                    "user_id" => $checkTrip->driver_id,
                    "booked_user_id" => $userData->id,
                    'is_read' => 0,
                    'title' => "Ride Bid",
                    'bid_id' => $request->bidId,
                    'trip_id' => $request->tripId,
                    'message' => $message,
                    'type' => 1,
                    'ride_trip_type' => 2,
                ]);
            }
            return CustomHelper::SuccessWithoutData("You are accept Bid successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function bidTripReject(Request $request)
    {
        try {
            $checkTrip = TripBid::where('trip_id', $request->tripId)->where('status', 0)->first();
            if ($checkTrip) {
                TripBid::where('id', $request->bidId)->update(['status' => 2]);
                $deviceToken = User::where('id', $checkTrip->driver_id)->first();
                $userData = CustomHelper::CheckUserExits();
                if ($deviceToken) {
                    $message = 'Ride Bid rejected by ' . $userData->name;
                    $tokens = [$deviceToken->device_token];
                    CustomHelper::NotifyMultipleUsers($tokens, 'Your Ride Bid', $message, ['tripId' => $request->tripId]);
                }
                Notification::where([
                    "user_id" => $request->userId,
                    'bid_id' => $request->bidId,
                    'trip_id' => $request->tripId,
                ])->delete();

                // Notification::insert([
                //     "user_id" => $checkTrip->driver_id,
                //     "booked_user_id" => $userData->id,
                //     'is_read' => 0,
                //     'title' => "Ride Bid Rejected",
                //     'bid_id' => $request->bidId,
                //     'trip_id' => $request->tripId,
                //     'message' => $message,
                //     'type' => 1,
                //     'ride_trip_type' => 2,
                // ]);
            }
            return CustomHelper::SuccessWithoutData("Your bid is rejected successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function tripStatusUpdate(Request $request)
    {
        $tripData = TripRequest::where('id', $request->tripId)->first();
        $tripBid = TripBid::where('trip_id', $request->tripId)->where('driver_id', $tripData->driver_id)->first();
        // cancel request start
        if ($request->status == 4) {
            $isDriver = 0;
            if ($tripBid && $tripBid->driver_id == $request->userId) {
                $isDriver = 1;
            }
            // dd($tripData);

            // Booking Status
            // 1 = Pending
            // 2 = Approved
            // 3 = Completed 
            // 4 = Cancelled
            // 5 = Rejected
            // 6 = Expired
            // 7 = No Show
            // 8 = Withdrawn

            // Cancel Type
            // 1 = request_withdrawn
            // 2 = request_expired
            // 3 = normal
            // 4 = late
            // 5 = driver
            // 6 = support_override
            // 7 = no_show

            $departureTime = Carbon::parse($tripData->requested_date);
            $now = now();

            // Already departed
            if ($now->gte($departureTime) && !$isDriver) {
                return CustomHelper::ErrorResponse('Trip has already departed.');
            }

            $hoursRemaining = $now->diffInHours($departureTime, false);
            // CASE 5 : Driver Cancel

            if ($isDriver) {
                // Normal driver cancellation
                $tripBid->cancel_type = 5;
                $tripBid->status = 4;
                $tripBid->refund_seat_amount        = $tripBid->seat_price;
                $tripBid->refund_booking_fee_amount = $tripBid->booking_fee;
                $tripBid->cancelled_at = now();
                $tripData->driver_id = null;
                $tripData->status = 0;
                $tripData->save();
                $tripBid->save();
                return CustomHelper::SuccessWithoutData('Trip Cancel Successfully');
            } elseif (!$isDriver) {
                if ($tripBid) {
                    if ($tripBid->status == 1 || $tripBid->status == 2) {
                        if ($hoursRemaining <= 24) {
                            $tripBid->cancel_type = 4; // Late
                            $tripBid->status = 4;
                            $tripData->status = 4;
                            $tripBid->refund_seat_amount        = $tripBid->seat_price * 0.50;
                            $tripBid->refund_booking_fee_amount = 0;
                            $tripBid->driver_compensation = $tripBid->seat_price * 0.50;
                            $tripBid->is_late_cancellation = 1;
                            $tripBid->late_review_added    = 1;

                            // Create Auto Review...
                            // Review::create(...);

                            // Credit Driver Wallet...
                        } else {
                            // CASE 1 : Request Withdrawn
                            if ($tripBid) {
                                $tripBid->status = 4;
                            }
                            $tripData->status = 4;
                            $tripBid->cancel_type = 1;
                            $tripBid->refund_seat_amount        = $tripBid->seat_price;
                            $tripBid->refund_booking_fee_amount = $tripBid->booking_fee;
                        }
                    }
                    $tripBid->save();
                }
                $tripData->status = 4;
                $tripData->save();
                return CustomHelper::SuccessWithoutData('Trip Cancel Successfully');
            } elseif ($tripBid->status == 6 && !$isDriver) {
                // CASE 2 : Request Expired
                $tripBid->status = 4;
                $tripData->status = 4;
                $tripBid->cancel_type = 2;
                $tripBid->refund_seat_amount        = $tripBid->seat_price;
                $tripBid->refund_booking_fee_amount = $tripBid->booking_fee;
            } elseif ($tripBid->cancel_by == 'support') {
                $tripBid->status = 4;
                $tripData->status = 4;
                $tripBid->cancel_type = 6;
                $tripBid->refund_seat_amount        = $tripBid->seat_price;
                $tripBid->refund_booking_fee_amount = $tripBid->booking_fee;
            } elseif ($tripBid->status == 7 && !$isDriver) {
                // CASE 7 : Passenger No Show
                $tripBid->cancel_type = 7;
                $tripBid->refund_seat_amount        = 0;
                $tripBid->refund_booking_fee_amount = 0;
                $tripBid->driver_compensation       = 0;
                $tripBid->status = 4;
                $tripData->status = 4;
            }

            $tripBid->cancelled_at = now();

            $totalRefund = $tripBid->refund_seat_amount + $tripBid->refund_booking_fee_amount;
            if ($totalRefund > 0) {
                // Refund Passenger Wallet
            }
            if ($tripBid->driver_compensation > 0) {
                // Credit Driver Wallet
            }
            $tripData->status = 4;
            $tripData->save();
            return CustomHelper::SuccessWithoutData('Trip Cancel Successfully');
        }
        // cancel request end
        $tripData->status = $request->status;
        $tripBid->status = $request->status;
        if ($request->status == 2) {
            $pin = rand(1000, 9999);
            $tripData->pin_start = $pin;
        }
        $tripData->save();
        $tripBid->save();
        $deviceToken = User::where('id', $tripData->passenger_id)->first();
        switch ($request->status) {
            case 1:
                $message = "Your Ride is now confirm";
                break;
            case 2:
                $message = "Your Ride is now Active";
                break;
            case 3:
                $message = "Your Ride is now Completed";
                break;
            case 4:
                $message = "Your Ride is Cancelled";
                break;
            default:
                $message = "Your Ride is now Active";
                break;
        }
        if ($deviceToken) {
            $tokens = [$deviceToken->device_token];
            CustomHelper::NotifyMultipleUsers($tokens, 'Ride updates', $message, ['tripId' => $request->tripId]);
        }

        Notification::insert([
            "user_id" => $tripData->passenger_id,
            "booked_user_id" => $request->userId,
            'is_read' => 0,
            'title' => "Your Ride status is updated",
            'trip_id' => $request->tripId,
            'message' => $message,
            'type' => 2,
            'ride_trip_type' => 2,
        ]);
        return CustomHelper::SuccessWithoutData("You are confirm successfully");
    }
    public function tripRideStatusUpdateCron(Request $request)
    {
        // Update all past TripRequests where status is 1 or 4
        TripRequest::whereIn('status', [0, 1, 2])->get()->each(function ($tripRequest) {
            if (Carbon::parse($tripRequest->requested_date)->isPast()) {
                $tripRequest->status = 4;
                $tripRequest->save();
            }
        });

        // Update all past Trips where status is 1 or 4
        Ride::whereIn('status', [0, 1, 2])->get()->each(function ($trip) {
            if (Carbon::parse($trip->departure_time)->isPast()) {
                $trip->status = 4;
                $trip->save();
            }
        });
    }
}
