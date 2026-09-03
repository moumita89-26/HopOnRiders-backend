<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\TripBid;
use App\Models\TripRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;


class TripRequestController extends Controller
{
    public function requestTrip(Request $request)
    {
        try {
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id',
                'pickupPoint' => 'required',
                'dropoffPoint' => 'required',
                'dropoffLat' => 'required',
                'dropoffLong' => 'required',
                'pickupLat' => 'required',
                'pickupLong' => 'required',
                'requestedDate' => 'required',
                'seatsRequired' => 'required',
                'luggageCount' => 'required',
                'message' => 'required',
            ]);
            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }

            $tripRequest = TripRequest::insertGetId([
                "passenger_id" => $request->userId,
                "pickup_point" => $request->pickupPoint,
                "dropoff_point" => $request->dropoffPoint,
                "dropoff_lat" => $request->dropoffLat,
                "dropoff_long" => $request->dropoffLong,
                "pickup_lat" => $request->pickupLat,
                "pickup_long" => $request->pickupLong,
                "requested_date" => $request->requestedDate,
                "seats_required" => $request->seatsRequired,
                "luggage_count" => $request->luggageCount,
                "message" => $request->message,
                "cart_type" => $request->cartType,
                "ac" => $request->ac,
                "luggage" => $request->luggage,
                "chargin" => $request->chargin,
                "music" => $request->music,
                "pets" => $request->pets,
            ]);
            if ($tripRequest) {
                $data  = TripRequest::with('carDetails', 'userDetails')->where('id', $tripRequest)->first();
                $data->isRide = 0;
                $data->isMyRide = $request->userId ==  $data->passenger_id ? 1 : 0;
                $data->isAccepted = 0;
                return CustomHelper::SuccessResponse("Ride created successfully", CustomHelper::CapitalizeArray($data->toArray()));
            }
            return CustomHelper::ErrorResponse("Something went wrong");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function editRequestedTrip(Request $request)
    {
        try {
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id',
                'requestId' => 'required',
                'pickupPoint' => 'required',
                'dropoffPoint' => 'required',
                'dropoffLat' => 'required',
                'dropoffLong' => 'required',
                'pickupLat' => 'required',
                'pickupLong' => 'required',
                'requestedDate' => 'required',
                'seatsRequired' => 'required',
                'luggageCount' => 'required',
            ]);
            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }
            $tripRequest = TripRequest::where('id', $request->requestId)->update([
                "passenger_id" => $request->userId,
                "pickup_point" => $request->pickupPoint,
                "dropoff_point" => $request->dropoffPoint,
                "dropoff_lat" => $request->dropoffLat,
                "dropoff_long" => $request->dropoffLong,
                "pickup_lat" => $request->pickupLat,
                "pickup_long" => $request->pickupLong,
                "requested_date" => $request->requestedDate,
                "seats_required" => $request->seatsRequired,
                "luggage_count" => $request->luggageCount,
                "message" => $request->message,
                "cart_type" => $request->cartType,
                "ac" => $request->ac,
                "luggage" => $request->luggage,
                "chargin" => $request->chargin,
                "music" => $request->music,
                "pets" => $request->pets,
            ]);
            if ($tripRequest) {
                return CustomHelper::SuccessWithoutData("Requested Ride Update Successfully");
            }
            return CustomHelper::ErrorResponse("Something went wrong");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function requestedTripDetails(Request $request)
    {
        try {
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id',
                'requestId' => 'required',
            ]);
            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }
            $tripRequest = TripRequest::with('carDetails', 'userDetails')->where('id', $request->requestId)->first();
            $tripRequest->isBid = 0;
            $tripRequest->bidPrice = 0;
            $tripRequest->confirmBidStatus = 0;
            $tripRequest->driverId = "";
            $tripRequest->isMyTrip = 0;
            $tripBidData = TripBid::where('trip_id', $request->requestId)->first();
            if ($tripBidData) {
                $tripRequest->isBid = 1;
                $tripRequest->isMyTrip = $tripRequest->passenger_id == $request->userId ? 1 : 0;
                // $tripBidData = TripBid::where('trip_id', $request->requestId)->where('driver_id', $request->userId)->first();
                if ($tripBidData->status >= 1) {
                    $tripRequest->bidPrice = $tripBidData->proposed_fare;
                    $tripRequest->driverId = $tripBidData->driver_id;
                    $tripRequest->confirmBidStatus = 1;
                }
            }
            if ($tripRequest) {
                return CustomHelper::SuccessResponse("Requested Ride Details Fetch Successfully", CustomHelper::CapitalizeArray($tripRequest->toArray()));
            }
            return CustomHelper::ErrorResponse("Requested Ride not found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function requestedTripList(Request $request)
    {
        try {
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id'
            ]);
            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }

            $newTime = Carbon::now()->subMinutes(1);
            $userId = $request->userId;
            // need add restriction to user role one only allow to get data
            $checkUser = CustomHelper::CheckUserExits();

            if ($checkUser->role == 2) {
                return CustomHelper::ErrorResponse("Data Not Found");
            }

            $tripRequest = TripRequest::with('carDetails', 'userDetails')
                ->where(function ($query) use ($userId) {
                    $query->whereHas('bids', function ($q) use ($userId) {
                        $q->where('driver_id', $userId)
                            ->whereIn('status', [0, 1, 2]);
                    })
                        ->orWhereDoesntHave('bids', function ($q) use ($userId) {
                            $q->where('driver_id', $userId);
                        });
                })
                ->where('trip_requests.status', 0)
                ->where('trip_requests.requested_date', '>=', $newTime)
                ->where('passenger_id', '!=', $userId)
                ->orderByDesc('id')
                ->paginate(10);
            if (count($tripRequest) > 0) {
                $tripList = $tripRequest->toArray();
                return CustomHelper::SuccessResponse('Requested Trips Fetch Successfully', CustomHelper::CapitalizeArray($tripList['data']), ['currentPage' => $tripRequest->currentPage(), 'totalPage' => $tripRequest->lastPage()]);
            }
            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
}
