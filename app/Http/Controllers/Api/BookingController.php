<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Ride;
use App\Models\TripRequest;
use App\Models\User;
use App\Services\DriverPayoutService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function bookRide(Request $request)
    {
        try {
            $userData = CustomHelper::CheckUserExits();
            $checkBooking = Booking::where('trip_id', $request->tripId)->where('passenger_id', $request->userId)->first();
            if ($checkBooking) {
                return CustomHelper::ErrorResponse('This ride has already been canceled by you previously, You can not book this ride.');
            }

            Booking::insert([
                'trip_id' => $request->tripId,
                'passenger_id' => $request->userId,
                'seats_booked' => $request->seatsBooked,
                'total_fare' => $request->totalFare,
                'seat_price' => $request->seatPrice,
                'booking_fee' => $request->bookingFee,
                'status' => 1,
            ]);

            $userId = Ride::where('id', $request->tripId)->first();
            $deviceToken = User::where('id', $userId->driver_id)->first();

            if ($deviceToken) {
                $message = 'Your Ride Booked by '.$userData->name;
                $tokens = [$deviceToken->device_token];
                CustomHelper::NotifyMultipleUsers($tokens, 'Your Ride Booked', $message, ['tripId' => $request->tripId]);
            }

            Notification::insert([
                'user_id' => $userId->driver_id,
                'booked_user_id' => $userData->id,
                'is_read' => 0,
                'title' => 'Your Ride is Booked',
                'ride_id' => $request->tripId,
                'message' => $message,
                'type' => 1,
            ]);

            return CustomHelper::SuccessWithoutData('Your Booking submitted successfully');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function verifyStartPin(Request $request)
    {
        $checkPin = Booking::where('passenger_id', $request->passengerId)->where('trip_id', $request->tripId)->first();
        if ($checkPin->pin_start == $request->pin) {
            $pinNew = rand(1000, 9999);
            Booking::where('passenger_id', $request->passengerId)->where('trip_id', $request->tripId)->update(['is_verify_spin' => 1, 'pin_end' => $pinNew]);

            return CustomHelper::SuccessWithoutData('Your pin verified successfully');
        }

        return CustomHelper::ErrorResponse('Your pin is incorrect');
    }

    public function verifyEndPin(Request $request)
    {
        $checkPin = Booking::where('passenger_id', $request->passengerId)->where('trip_id', $request->tripId)->first();
        if ($checkPin->pin_end == $request->pin) {
            $checkPin->is_verify_epin = 1;
            $checkPin->status = 3;
            $checkPin->save();

            try {
                app(DriverPayoutService::class)->createPendingForCompletedBooking($checkPin->fresh());
            } catch (\Throwable $exception) {
                // Settlement recording must not change the existing PIN API response.
                report($exception);
            }

            return CustomHelper::SuccessWithoutData('Your pin verified successfully');
        }

        return CustomHelper::ErrorResponse('Your pin is incorrect');
    }

    public function verifyTripStartPin(Request $request)
    {
        $tripData = TripRequest::where('id', $request->tripId)->first();
        if ($tripData->pin_start == $request->pin) {
            $pin = rand(1000, 9999);
            $tripData->is_verify_spin = 1;
            $tripData->pin_end = $pin;
            $tripData->save();

            return CustomHelper::SuccessWithoutData('Your pin verified successfully');
        }

        return CustomHelper::ErrorResponse('Your pin is incorrect');
    }

    public function verifyTripEndPin(Request $request)
    {
        $tripData = TripRequest::where('id', $request->tripId)->first();
        if ($tripData->pin_end == $request->pin) {
            $tripData->is_verify_epin = 1;
            $tripData->status = 3;
            $tripData->save();

            try {
                app(DriverPayoutService::class)->createPendingForCompletedTripRequest($tripData->fresh());
            } catch (\Throwable $exception) {
                // Settlement recording must not change the existing PIN API response.
                report($exception);
            }

            return CustomHelper::SuccessWithoutData('Your pin verified successfully');
        }

        return CustomHelper::ErrorResponse('Your pin is incorrect');
    }
}
