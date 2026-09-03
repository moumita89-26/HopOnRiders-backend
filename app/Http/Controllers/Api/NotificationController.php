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
use stdClass;

class NotificationController extends Controller
{
    public function notificationList(Request $request)
    {
        $error = CustomHelper::ValidateField([
            'userId' => 'required',
        ]);
        if ($error) {
            return CustomHelper::ErrorResponse($error);
        }
        $checkUser = CustomHelper::CheckUserExits();
        if (!$checkUser) {
            return CustomHelper::ErrorResponse('User Not found');
        }
        $userId = $request->userId;
        $notificationList = Notification::where(['user_id' => $userId])->orderBy('id', 'desc')->paginate(12);
        foreach ($notificationList as $notification) {
            $notification->isYourRide = 0;
            if ($notification->trip_id) {
                $trip =  TripRequest::where('id', $notification->trip_id)->first();
                $notification->tripData = $trip ? $trip->toArray() : "";
                $notification->isYourRide = $trip && $trip->driver_id == $userId ? 1 : 0;
            } else {
                $notification->tripData = "";
            }
            if ($notification->ride_id) {
                $trip =  Ride::where('id', $notification->ride_id)->first();
                $notification->rideData = $trip ? $trip->toArray() : "";
                $notification->isYourRide = $trip && $trip->driver_id == $userId ? 1 : 0;
            } else {
                $notification->rideData = "";
            }
            if ($notification->bid_id) {
                $bidData = TripBid::where('id', $notification->bid_id)->first();
                $notification->bidData = $bidData ? $bidData->toArray() : "";
            } else {
                $notification->bidData = "";
            }
        }
        $notificationListNew = $notificationList->toArray();
        if ($notificationList) {
            return CustomHelper::SuccessResponse('Notifications Fetch Successfully', CustomHelper::CapitalizeArray($notificationListNew['data']), ['currentPage' => $notificationList->currentPage(), 'totalPage' => $notificationList->lastPage()]);
        } else {
            return CustomHelper::ErrorResponse('Data not found');
        }
    }
    public function notificationRead(Request $request)
    {
        $error = CustomHelper::ValidateField([
            'notificationId' => 'required',
        ]);
        if ($error) {
            return CustomHelper::ErrorResponse($error);
        }
        $checkUser = CustomHelper::CheckUserExits();
        if (!$checkUser) {
            return CustomHelper::ErrorResponse('User Not found');
        }
        $reviewList = Notification::where(['id' => $request->notificationId])->update(['is_read' => 1]);
        return CustomHelper::SuccessWithoutData('Notifications Read Successfully');
    }
    public function notificationCount(Request $request)
    {
        $error = CustomHelper::ValidateField([
            'userId' => 'required',
        ]);
        if ($error) {
            return CustomHelper::ErrorResponse($error);
        }
        $checkUser = CustomHelper::CheckUserExits();
        if (!$checkUser) {
            return CustomHelper::ErrorResponse('User Not found');
        }
        $NotificationCount = Notification::where(['user_id' => $request->userId, 'is_read' => 0])->count();
        return CustomHelper::SuccessResponse('Notifications Count Successfully', (string) $NotificationCount);
    }

    public function sendNotification(Request $request)
    {
        $deviceToken = User::where('id', $request->toUserId)->first();
        if ($deviceToken) {
            $message = $request->message;
            $tokens = [$deviceToken->device_token];
            CustomHelper::NotifyMultipleUsers($tokens, 'New Message', $message, ['tripId' => $request->tripId]);
        }
        Notification::where("user_id", $request->toUserId)->where("booked_user_id", $request->userId)->where('trip_id', $request->tripId)->where('n_type', 2)->update([
            "user_id" =>  $request->toUserId,
            "booked_user_id" => $request->userId,
            'is_read' => 0,
            'title' => "New Message",
            'ride_id' => $request->type == 1 ? $request->tripId : '',
            'trip_id' => $request->type == 2 ? $request->tripId : '',
            'message' => $message,
            'type' => $request->type,
            'is_my_ride' => $request->isMyRide,
            'ride_trip_type' => $request->type,
            'n_type' => 2
        ]);
        return CustomHelper::SuccessWithoutData('Notifications Send Successfully');
    }
}
