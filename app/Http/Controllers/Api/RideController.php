<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Rating;
use App\Models\Settings;
use App\Models\Ride;
use App\Models\TripBid;
use App\Models\TripRequest;
use App\Models\User;
use App\Models\WayPoint;
use App\Services\DriverSettlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RideController extends Controller
{
    public function createRide(Request $request)
    {
        try {
            $checkUser = CustomHelper::CheckUserExits();
            if ($checkUser->is_suspend) {
                return CustomHelper::ErrorResponse("Your Account has been suspended. Please contact admin");
            }
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id',
                'origin' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
                'wayPoints' => 'nullable|array',
                'departureTime' => 'required',
                'farePerSeat' => 'required|min:0',
            ]);

            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }

            $trip = Ride::insertGetId([
                'driver_id' => $request->userId,
                'kilometer' => $request->kilometer,
                'origin' =>  $request->origin,
                'destination' =>  $request->destination,
                'des_lat' =>  $request->desLat,
                'des_long' =>  $request->desLong,
                'origin_lat' =>  $request->originLat,
                'origin_long' =>  $request->originLong,
                'departure_time' =>  $request->departureTime,
                'total_seats' =>  $request->availableSeats,
                'fare_per_seat' =>  $request->farePerSeat,
                'message' =>  $request->message,
                'status' =>  1,
            ]);

            if ($trip) {
                foreach ($request->wayPoints as $point) {
                    WayPoint::insert([
                        "trip_id" => $trip,
                        "lat" => $point['lat'],
                        "long" => $point['long'],
                        "destination" => $point['destination'],
                    ]);
                }
                return CustomHelper::SuccessResponse("Ride Create Successfully", $trip);
            }
            return CustomHelper::ErrorResponse("Something went wrong");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function editRide(Request $request)
    {
        try {
            $checkUser = CustomHelper::CheckUserExits();
            if ($checkUser->is_suspend) {
                return CustomHelper::ErrorResponse("Your Account has been suspended. Please contact admin");
            }
            $checkError =  CustomHelper::ValidateField([
                'userId' => 'required|exists:users,id',
                'origin' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
                'wayPoints' => 'nullable|array',
                'departureTime' => 'required',
                'farePerSeat' => 'required|min:0',
            ]);

            if ($checkError) {
                return CustomHelper::ErrorResponse($checkError);
            }

            $trip = Ride::where('id', $request->rideId)->update([
                'driver_id' => $request->userId,
                'origin' =>  $request->origin,
                'destination' =>  $request->destination,
                'kilometer' => $request->kilometer,
                'des_lat' =>  $request->desLat,
                'des_long' =>  $request->desLong,
                'origin_lat' =>  $request->originLat,
                'origin_long' =>  $request->originLong,
                'departure_time' =>  $request->departureTime,
                'total_seats' =>  $request->availableSeats,
                'fare_per_seat' =>  $request->farePerSeat,
                'message' =>  $request->message,
                'status' =>  1,
            ]);

            if ($trip) {
                WayPoint::where('trip_id', $request->rideId)->delete();
                foreach ($request->wayPoints as $point) {
                    WayPoint::insert([
                        "trip_id" => $request->rideId,
                        "lat" => $point['lat'],
                        "long" => $point['long'],
                        "destination" => $point['destination'],
                    ]);
                }
                return CustomHelper::SuccessResponse("Ride update Successfully", $request->rideId);
            }

            return CustomHelper::ErrorResponse("Something went wrong");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function rideList(Request $request)
    {
        try {

            $responseData = [];
            $newTime = Carbon::now()->subMinutes(1);
            $trip = Ride::with('wayPoint', 'driverDetails')->where('rides.departure_time', '>=', $newTime)->where('status', 1)->whereNot('driver_id', $request->userId)->orderBy('id', 'desc')->get();

            foreach ($trip as $tp) {
                $ratingData = Rating::select('rating')
                    ->where('driver_id', $tp->driver_id)
                    ->orderBy('rating', 'desc')
                    ->avg('rating');
                $tp->review = Rating::where('driver_id', $tp->driver_id)
                    ->orderBy('id', 'desc')
                    ->get()->toArray();
                $tp->rating = ($ratingData) ? round($ratingData) : 0;
                $booked = Booking::where('trip_id', $tp->id)->whereNot('status', 4)->sum('seats_booked');
                $alredyBooked = Booking::where('trip_id', $tp->id)->where('passenger_id', $request->userId)->first();
                $tp->available_seats = $available =  $tp->total_seats - $booked;
                if ($available <= 0) {
                    continue;
                }
                if ($alredyBooked) {
                    continue;
                }
                array_push($responseData, $tp->toArray());
            }

            if (count($trip) > 0) {
                // $tripList = $trip->toArray();
                return CustomHelper::SuccessResponse('Rides Fetch Successfully', CustomHelper::CapitalizeArray($responseData));
            }
            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function searchRide(Request $request)
    {
        try {
            $destination = $request->destination;
            $origin = $request->origin;
            $departureTime = $request->departureTime;

            // Search for rides (as driver offers)
            $rides = Ride::with('wayPoint', 'driverDetails')
                ->whereNot('driver_id', $request->userId)
                ->when($destination, function ($destQuery) use ($destination) {
                    $destQuery->where('rides.destination', 'like', '%' . $destination . '%');
                })
                ->when($origin, function ($oriQuery) use ($origin) {
                    $oriQuery->where('rides.origin', 'like', '%' . $origin . '%');
                })
                ->where('status', 1)
                ->where('rides.departure_time', '>=', Carbon::now())
                ->when($departureTime, function ($depQuery) use ($departureTime) {
                    $depQuery->whereDate('rides.departure_time', $departureTime);
                })
                ->orderBy('id', 'desc')
                ->paginate(12);

            // Search for trip requests (passenger requests)
            $tripRequests = TripRequest::with('carDetails', 'userDetails')
                ->whereNot('passenger_id', $request->userId)
                ->when($destination, function ($destQuery) use ($destination) {
                    $destQuery->where('dropoff_point', 'like', '%' . $destination . '%');
                })
                ->when($origin, function ($oriQuery) use ($origin) {
                    $oriQuery->where('pickup_point', 'like', '%' . $origin . '%');
                })
                ->where('requested_date', '>=', Carbon::now())
                ->when($departureTime, function ($depQuery) use ($departureTime) {
                    $depQuery->whereDate('requested_date', $departureTime);
                })
                ->orderBy('id', 'desc')
                ->paginate(12);
            // Process rides (driver offers)
            $tripsArray = $rides->map(function ($trip) {
                $ratingData = Rating::select('rating')
                    ->where('driver_id', $trip->driver_id)
                    ->orderBy('rating', 'desc')
                    ->avg('rating');

                $trip->review = Rating::where('driver_id', $trip->driver_id)
                    ->orderBy('id', 'desc')
                    ->get()->toArray();

                $trip->rating = ($ratingData) ? round($ratingData) : 0;
                $booked =  Booking::where('trip_id', $trip->id)->whereNot('status', 4)->sum('seats_booked');
                $trip->available_seats = $trip->available_seats - $booked;
                $trip->type = '1'; // Mark as trip offer
                return $trip->toArray(); // Convert to array
            });

            // Process trip requests (passenger requests)
            $tripRequestsArray = $tripRequests->map(function ($request) {
                $request->type = '2'; // Mark as trip request
                return $request->toArray(); // Convert to array
            });

            // Merge results as arrays
            $mergedResults = $tripsArray->concat($tripRequestsArray);

            // Sort by ID descending (or any other criteria you prefer)
            $sortedResults = $mergedResults->sortByDesc('id');

            // Paginate manually since we merged two paginated collections
            $perPage = 12;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $sortedResults->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $paginatedResults = new LengthAwarePaginator($currentItems, $sortedResults->count(), $perPage, $currentPage);
            if (count($paginatedResults) > 0) {
                $resultData = $paginatedResults->toArray();
                $resultData['data'] = array_values($resultData['data']);
                return CustomHelper::SuccessResponse(
                    'Rides and Requests Fetched Successfully',
                    CustomHelper::CapitalizeArray($resultData['data']),
                    [
                        'currentPage' => $paginatedResults->currentPage(),
                        'totalPage' => $paginatedResults->lastPage()
                    ]
                );
            }

            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function searchRideUser(Request $request)
    {
        try {
            $destination = $request->destination;
            $origin = $request->origin;
            $departureTime = $request->departureTime;
            $trip = Ride::with('wayPoint', 'driverDetails')
                ->when($destination, function ($destQuery) use ($destination) {
                    $destQuery->where('rides.destination', 'like', '%' . $destination . '%');
                })
                ->when($origin, function ($oriQuery) use ($origin) {
                    $oriQuery->where('rides.origin', 'like', '%' . $origin . '%');
                })
                ->when($departureTime, function ($depQuery) use ($departureTime) {
                    $depQuery->whereDate('rides.departure_time',  $departureTime);
                })
                ->where('rides.departure_time', '>=', Carbon::now())
                ->where('driver_id', $request->userId)
                ->orderBy('id', 'desc')->paginate(12);
            foreach ($trip as $tp) {
                $ratingData = Rating::select('rating')
                    ->where('driver_id', $tp->driver_id)
                    ->orderBy('rating', 'desc')
                    ->avg('rating');
                $tp->review = Rating::where('driver_id', $tp->driver_id)
                    ->orderBy('id', 'desc')
                    ->get()->toArray();
                $tp->rating = ($ratingData) ? round($ratingData) : 0;
                $booked =  Booking::where('trip_id', $tp->id)->whereNot('status', 4)->sum('seats_booked');
                $tp->available_seats = $tp->available_seats - $booked;
            }
            if (count($trip) > 0) {
                $tripList = $trip->toArray();
                return CustomHelper::SuccessResponse('Rides Fetch Successfully', CustomHelper::CapitalizeArray($tripList['data']), ['currentPage' => $trip->currentPage(), 'totalPage' => $trip->lastPage()]);
            }
            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function searchTrip(Request $request)
    {
        try {
            $destination = $request->destination;
            $origin = $request->origin;
            $departureTime = $request->departureTime;
            $trip = TripRequest::with('carDetails', 'userDetails')->whereNot('passenger_id', $request->userId)
                ->when($destination, function ($destQuery) use ($destination) {
                    $destQuery->where('rides.dropoff_point', 'like', '%' . $destination . '%');
                })
                ->when($origin, function ($oriQuery) use ($origin) {
                    $oriQuery->where('rides.pickup_point', 'like', '%' . $origin . '%');
                })
                ->where('trip_requests.requested_date', '>=', Carbon::now())
                ->when($departureTime, function ($depQuery) use ($departureTime) {
                    $depQuery->whereDate('trip_requests.requested_date',  $departureTime);
                })->orderBy('id', 'desc')->paginate(12);
            if (count($trip) > 0) {
                $tripList = $trip->toArray();
                return CustomHelper::SuccessResponse('Rides Fetch Successfully', CustomHelper::CapitalizeArray($tripList['data']), ['currentPage' => $trip->currentPage(), 'totalPage' => $trip->lastPage()]);
            }
            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function rideDetails(Request $request)
    {
        try {
            $trip = Ride::with('wayPoint', 'driverDetails')->where('id', $request->rideId)->orderBy('id', 'desc')->first();
            if (!$trip) {
                return CustomHelper::ErrorResponse("Ride not found");
            }
            $booked =  Booking::where('trip_id', $trip->id)->whereNot('status', 4)->sum('seats_booked');
            $isDropOff =  Booking::where('trip_id', $request->rideId)->where('passenger_id', $request->userId)->first();
            $trip->booked_seats = $booked;
            $userBooked = Booking::where('trip_id', $trip->id)->where('passenger_id', $request->userId)->sum('seats_booked');
            $trip->isRide = 1;
            $trip->isMyRide = 1;
            $trip->is_drop_off = $isDropOff ? $isDropOff->is_drop_off : 0;
            $trip->isAccepted = $booked > 0 ? 1 : 0;
            // payment calculation
            $farePerSeat = $trip ? $trip->fare_per_seat : 0;
            $totalFare = $farePerSeat * $userBooked;
            $percentage = Settings::find(1);
            $bookingFee = ((float)$totalFare * (float)$percentage->booking_fee) / 100;
            $trip->bookingFeePercentage = $percentage->booking_fee;
            $trip->totalFare = $totalFare;
            $trip->pBookedSeats = $userBooked;
            $trip->bookingFee = $bookingFee;
            $trip->totalPaidAmount = $bookingFee + $totalFare;
            // end payment calculation

            $ratingData = Rating::select('rating')
                ->where('driver_id', $trip->driver_id)
                ->orderBy('rating', 'desc')
                ->avg('rating');
            $trip->review = Rating::where('driver_id', $trip->driver_id)
                ->orderBy('id', 'desc')
                ->get()->toArray();
            $trip->rating = ($ratingData) ? round($ratingData) : 0;
            $trip->availableSeats = $trip->total_seats - $booked;
            if ($trip->driver_id == $request->userId) {
                $trip->bookings = $booking = Booking::with('userData')->where('trip_id', $request->rideId)->where('status', '!=', 4)->get()->toArray();
            } else {
                $trip->bookings = Booking::with('userData')->where('trip_id', $request->rideId)->where('passenger_id', $request->userId)->where('status', '!=', 4)->get()->toArray();
            }
            if ($trip) {
                return CustomHelper::SuccessResponse('Ride Fetch Successfully', CustomHelper::CapitalizeArray($trip->toArray()));
            }
            return CustomHelper::ErrorResponse("Data Not Found");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function activeUpcoming(Request $request)
    {
        $finalData = [];
        $userId = $request->userId;
        $status = $request->status;
        $role = $request->role; // 1 for driver, 2 for passenger

        // Common function to add rating and review data
        $addRatingData = function ($item, $driverId) {
            $ratingData = Rating::select('rating')
                ->where('driver_id', $driverId)
                ->orderBy('rating', 'desc')
                ->avg('rating');
            $item->review = Rating::where('driver_id', $driverId)
                ->orderBy('id', 'desc')
                ->get()->toArray();
            $item->rating = ($ratingData) ? round($ratingData) : 0;
            return $item;
        };

        if ($role == 1) { // Driver role
            // Get rides as driver
            $ridesAsDriver = Ride::with('wayPoint', 'driverDetails')
                ->where('driver_id', $userId)
                ->orderBy('created_at', 'desc')
                ->where('status', $status)
                ->get();

            // Process rides as driver
            foreach ($ridesAsDriver as $ride) {
                $ride = $addRatingData($ride, $ride->driver_id);
                $ride->isRide = 1;
                $ride->isMyRide = 1; // Since user is the driver
                $ride->isAccepted = 1; // Driver's own ride is always accepted
                $finalData[] = $ride->toArray();
            }

            // Get trip requests where driver has placed bids
            if ($status == 4 || $status == 3) {

                $tripsAsPotentialDriver = TripRequest::with('carDetails', 'userDetails')
                    ->where('passenger_id', '!=', $userId)
                    ->whereHas('bids', function ($q) use ($userId, $status) {
                        $q->where('driver_id', $userId)
                            ->whereIn('status', [$status]);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $tripsAsPotentialDriver = TripRequest::with('carDetails', 'userDetails')
                    ->where('passenger_id', '!=', $userId)
                    ->whereHas('bids', function ($q) use ($userId, $status) {
                        $q->where('driver_id', $userId)
                            ->whereIn('status', [$status]);
                    })
                    ->orderBy('created_at', 'desc')
                    ->where('status', $status)
                    ->get();
            }

            // Process rides as potential driver
            foreach ($tripsAsPotentialDriver as $trip) {
                $trip->isRide = 0;
                $trip->isMyRide = 0; // Someone else created this trip
                $trip->isAccepted = TripBid::where('trip_id', $trip->id)
                    ->where('driver_id', $userId)
                    ->where('status', 2) // Assuming 2 is accepted status
                    ->exists() ? 1 : 0;
                $finalData[] = $trip->toArray();
            }
        } else {
            // Passenger role
            // Get rides as passenger
            $ridesAsPassenger = Ride::with('wayPoint', 'driverDetails')
                ->whereHas('booking', function ($q) use ($request) {
                    $q->where('passenger_id', $request->userId)
                        ->where('status', $request->status);
                })
                ->orderBy('created_at', 'asc')
                ->get();
            // Process rides as passenger
            foreach ($ridesAsPassenger as $ride) {
                $ride = $addRatingData($ride, $ride->driver_id);
                $ride->isRide = 1;
                $ride->isMyRide = 0; // Not the driver
                $ride->isAccepted = 1; // Booked ride is accepted
                $finalData[] = $ride->toArray();
            }

            // Get trip requests created by passenger
            if ($request->status == 1) {
                $tripsAsRequester = TripRequest::with('carDetails', 'userDetails')
                    ->where('passenger_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->whereIn('status', [0, $request->status])
                    ->get();
            } else {
                $tripsAsRequester = TripRequest::with('carDetails', 'userDetails')
                    ->where('passenger_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->where('status', $request->status)
                    ->get();
            }
            // Process rides as requester
            foreach ($tripsAsRequester as $trip) {
                $trip->isRide = 0;
                $trip->isMyRide = 1; // User created this trip request
                $trip->isAccepted = TripBid::where('trip_id', $trip->id)
                    ->where('status', 2) // Assuming 2 is accepted status
                    ->exists() ? 1 : 0;
                $finalData[] = $trip->toArray();
            }
        }

        if (count($finalData) > 0) {
            return CustomHelper::SuccessResponse(
                'Rides Fetched Successfully',
                CustomHelper::CapitalizeArray($finalData)
            );
        }

        return CustomHelper::ErrorResponse("Data Not Found");
    }


    public function myEarning(Request $request)
    {
        $userId = $request->userId;
        $isPaid = $request->isPaid;
        $setting = Settings::find(1);
        // Total earnings (Paid + Unpaid)
        $allRides = Ride::with(['booking'])->where('driver_id', $userId)->whereIn('status', [3, 4])->get();
        $allTrip = TripRequest::with(['bids' => function ($q) use ($userId) {
            $q->where('driver_id', $userId);
        }])->where('driver_id', $userId)->whereIn('status', [3, 4])->get();
        $driverTotalPayment = 0;
        foreach ($allRides as $ride) {
            $grossEarning = 0;
            foreach ($ride->booking as $booking) {
                if ($booking->status != 4) {
                    $grossEarning += $booking->seat_price;
                }
                $grossEarning += $booking->driver_compensation;
            }
            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $driverTotalPayment += ($grossEarning - $payoutFee);
        }

        foreach ($allTrip as $trip) {
            $grossEarning = 0;
            foreach ($trip->bids as $bid) {
                if ($bid->status != 4) {
                    $grossEarning += $bid->seat_price;
                }
                $grossEarning += $bid->driver_compensation;
            }
            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $driverTotalPayment += ($grossEarning - $payoutFee);
        }

        // List only requested payout status
        $ridesAsDriver = Ride::with(['booking'])
            ->where('driver_id', $userId)
            ->where('payout_status', $isPaid)
            ->whereIn('status', [3, 4])
            ->get();

        $tripAsDriver = TripRequest::with(['bids' => function ($q) use ($userId) {
            $q->where('driver_id', $userId);
        }])->where('payout_status', $isPaid)->where('driver_id', $userId)->whereIn('status', [3, 4])->get();


        foreach ($ridesAsDriver as $ride) {
            $grossEarning = 0;
            foreach ($ride->booking as $booking) {
                if ($booking->status != 4) {
                    $grossEarning += $booking->seat_price;
                }
                $grossEarning += $booking->driver_compensation;
            }
            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $ride->PayoutFee       = number_format($payoutFee, 2);
            $ride->totalPaidAmount = $grossEarning;
            $ride->driverPayout    = number_format($grossEarning - $payoutFee, 2);
        }

        foreach ($tripAsDriver as $trip) {
            $grossEarning = 0;
            foreach ($trip->bids as $bid) {
                if ($bid->status != 4) {
                    $grossEarning += $bid->seat_price;
                }
                $grossEarning += $bid->driver_compensation;
            }

            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $trip->PayoutFee       = number_format($payoutFee, 2);
            $trip->totalPaidAmount = $grossEarning;
            $trip->driverPayout    = number_format($grossEarning - $payoutFee, 2);
        }

        $responseData = array_merge(
            $ridesAsDriver->toArray(),
            $tripAsDriver->toArray()
        );
        if (count($responseData) > 0) {
            return CustomHelper::SuccessResponse(
                'Rides Fetched Successfully',
                CustomHelper::CapitalizeArray($responseData),
                [
                    'driverTotalPayment' => number_format($driverTotalPayment, 2)
                ]
            );
        }

        return CustomHelper::ErrorResponse("Data Not Found", [
            'driverTotalPayment' => number_format($driverTotalPayment, 2)
        ]);
    }

    public function myTotalEarnings(Request $request, DriverSettlementService $settlementService)
    {
        $checkError = CustomHelper::ValidateField([
            'userId' => 'required|exists:users,id',
        ]);
        if ($checkError) {
            return CustomHelper::ErrorResponse($checkError);
        }

        $userId = $request->userId;
        $setting = Settings::find(1);
        $rides = Ride::with(['booking'])
            ->where('driver_id', $userId)
            ->whereIn('status', [3, 4])
            ->get();
        $trips = TripRequest::with(['bids' => function ($query) use ($userId) {
            $query->where('driver_id', $userId);
        }])
            ->where('driver_id', $userId)
            ->whereIn('status', [3, 4])
            ->get();

        foreach ($rides as $ride) {
            $grossEarning = 0;
            foreach ($ride->booking as $booking) {
                if ($booking->status != 4) {
                    $grossEarning += $booking->seat_price;
                }
                $grossEarning += $booking->driver_compensation;
            }
            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $ride->PayoutFee = number_format($payoutFee, 2);
            $ride->totalPaidAmount = $grossEarning;
            $ride->driverPayout = number_format($grossEarning - $payoutFee, 2);
        }

        foreach ($trips as $trip) {
            $grossEarning = 0;
            foreach ($trip->bids as $bid) {
                if ($bid->status != 4) {
                    $grossEarning += $bid->seat_price;
                }
                $grossEarning += $bid->driver_compensation;
            }
            $payoutFee = ($grossEarning * $setting->driver_payout_fee) / 100;
            $trip->PayoutFee = number_format($payoutFee, 2);
            $trip->totalPaidAmount = $grossEarning;
            $trip->driverPayout = number_format($grossEarning - $payoutFee, 2);
        }

        $responseData = array_merge($rides->toArray(), $trips->toArray());
        foreach ($responseData as &$item) {
            unset($item['payout_status']);
        }
        unset($item);
        usort($responseData, function ($first, $second) {
            return strtotime($first['created_at'] ?? '') <=> strtotime($second['created_at'] ?? '');
        });

        $settlementRecords = $settlementService->ledger((int) $userId);
        $summary = [
            'driverTotalPayment' => number_format((float) $settlementRecords->sum('earning_amount'), 2),
            'adminPaid' => number_format((float) $settlementRecords->sum('admin_paid_amount'), 2),
            'pendingPayout' => number_format((float) $settlementRecords->sum('outstanding_amount'), 2),
        ];

        if (count($responseData) > 0) {
            return CustomHelper::SuccessResponse(
                'Rides Fetched Successfully',
                CustomHelper::CapitalizeArray($responseData),
                $summary
            );
        }

        return CustomHelper::ErrorResponse('Data Not Found', $summary);
    }

    public function updateStatus(Request $request)
    {
        try {
            $trip = Ride::where('id', $request->tripId)->first();
            if ($request->status == 4) {
                $isDriver = 0;
                if ($trip->driver_id == $request->userId) {
                    $isDriver = 1;
                }
                $booking = Booking::where('passenger_id', $request->userId)->where('trip_id', $request->tripId)->first();

                if (!$booking && !$isDriver) {
                    return CustomHelper::ErrorResponse('Booking not found.');
                }

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

                $departureTime = Carbon::parse($trip->departure_time);
                $now = now();

                // Already departed
                if ($now->gte($departureTime) && !$isDriver) {
                    return CustomHelper::ErrorResponse('Ride has already departed.');
                }

                $hoursRemaining = $now->diffInHours($departureTime, false);
                // CASE 5 : Driver Cancel

                if ($isDriver) {
                    // Normal driver cancellation
                    $bookings = Booking::where('trip_id', $trip->id)->get();
                    foreach ($bookings as $book) {
                        $book->cancel_type = 5;
                        $book->status = 4;
                        $book->refund_seat_amount        = $book->seat_price;
                        $book->refund_booking_fee_amount = $book->booking_fee;
                        $book->cancelled_at = now();
                        $book->save();
                    }
                    $trip->status = 4;
                    $trip->save();
                    return CustomHelper::SuccessWithoutData('Ride Cancel Successfully by driver');
                } elseif ($booking->status == 1 || $booking->status == 2 && !$isDriver) {
                    if ($hoursRemaining <= 24) {
                        $booking->cancel_type = 4; // Late
                        $booking->status = 4;
                        $booking->refund_seat_amount        = $booking->seat_price * 0.50;
                        $booking->refund_booking_fee_amount = 0;
                        $booking->driver_compensation = $booking->seat_price * 0.50;
                        $booking->is_late_cancellation = 1;
                        $booking->late_review_added    = 1;

                        // Create Auto Review...
                        // Review::create(...);

                        // Credit Driver Wallet...
                    } else {
                        // CASE 1 : Request Withdrawn
                        $booking->status = 4;
                        $booking->cancel_type = 1;
                        $booking->refund_seat_amount        = $booking->seat_price;
                        $booking->refund_booking_fee_amount = $booking->booking_fee;
                    }
                } elseif ($booking->status == 6 && !$isDriver) {
                    // CASE 2 : Request Expired
                    $booking->status = 4;
                    $booking->cancel_type = 2;
                    $booking->refund_seat_amount        = $booking->seat_price;
                    $booking->refund_booking_fee_amount = $booking->booking_fee;
                } elseif ($booking->cancel_by == 'support') {
                    $booking->status = 4;
                    $booking->cancel_type = 6;
                    $booking->refund_seat_amount        = $booking->seat_price;
                    $booking->refund_booking_fee_amount = $booking->booking_fee;
                } elseif ($booking->status == 7 && !$isDriver) {
                    // CASE 7 : Passenger No Show
                    $booking->cancel_type = 7;
                    $booking->refund_seat_amount        = 0;
                    $booking->refund_booking_fee_amount = 0;
                    $booking->driver_compensation       = 0;
                    $booking->status = 4;
                }

                $booking->cancelled_at = now();
                $booking->save();

                /*
                |--------------------------------------------------------------------------
                | Process Wallet Transactions
                |--------------------------------------------------------------------------
                */

                $totalRefund = $booking->refund_seat_amount + $booking->refund_booking_fee_amount;
                if ($totalRefund > 0) {
                    // Refund Passenger Wallet
                }
                if ($booking->driver_compensation > 0) {
                    // Credit Driver Wallet
                }
                /*
                |--------------------------------------------------------------------------
                | Update Ride Seats & Status
                |--------------------------------------------------------------------------
                */
                $trip->available_seats += $booking->seats_booked;
                if ($trip->available_seats == $trip->total_seats) {
                    $trip->ride_status = 'available';
                } elseif ($trip->available_seats == 0) {
                    $trip->ride_status = 'full';
                } else {
                    $trip->ride_status = 'partially_booked';
                }
                $trip->save();
                return CustomHelper::SuccessWithoutData('Ride Cancel Successfully');
            }

            if ($request->status == 2) {
                $departureTime = Carbon::parse($trip->departure_time);
                $currentTime = Carbon::now();
                if ($currentTime->lt($departureTime)) {
                    $remainingMinutes = $currentTime->diffInMinutes($departureTime);
                    return CustomHelper::ErrorResponse("You can start the ride only after the departure time.", ['remaining_minutes' => $remainingMinutes,]);
                }
            }
            switch ($request->status) {
                case 1:
                    $message = "Your Ride now Active";
                    break;
                case 2:
                    $message = "Your Ride now Active";
                    break;
                case 3:
                    $message = "Your Ride now Completed";
                    break;
                case 4:
                    $message = "Your Ride Cancelled";
                    break;

                default:
                    $message = "Your Ride now Active";
                    break;
            }

            Booking::where('trip_id', $request->tripId)->where('status', '!=', 4)->update(["status" => $request->status]);
            $trip->status = $request->status;
            $trip->save();

            // send notification
            $userIds = Booking::where('trip_id', $request->tripId)->select('trip_id', 'passenger_id')->groupBy('trip_id', 'passenger_id')->get()->pluck('passenger_id');
            if ($request->status == 2) {
                foreach ($userIds as $userId) {
                    $pin = rand(1000, 9999);
                    Booking::where('trip_id', $request->tripId)->where('passenger_id', $userId)->update([
                        "pin_start" => $pin,
                    ]);
                }
            }

            $deviceToken = User::whereIn('id', $userIds)->whereNotNull('device_token')->get()->pluck('device_token');
            if (count($deviceToken) > 0) {
                CustomHelper::NotifyMultipleUsers($deviceToken->toArray(), 'Ride Status updated', $message, ['tripId' => $request->tripId]);
            }
            foreach ($userIds as $id) {
                Notification::insert([
                    "user_id" => $id,
                    'is_read' => 0,
                    'title' => "Your Ride Status is updated",
                    'ride_id' => $request->tripId,
                    'message' => $message,
                    'ride_trip_type' => 1,
                    'type' => 1
                ]);
            }
            // send notification end

            return CustomHelper::SuccessWithoutData("Your Ride Updated successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function updateRideDrop(Request $request)
    {
        try {
            Booking::where('trip_id', $request->rideId)->where('passenger_id', $request->userId)->update([
                'is_drop_off' => 1
            ]);
            // send notification
            $rideData = Ride::find($request->rideId);
            $userData = User::find($request->userId);
            $message = $userData->name . " has marked the ride as completed";
            $deviceToken = User::whereIn('id', [$rideData->driver_id])->whereNotNull('device_token')->get()->pluck('device_token');
            if (count($deviceToken) > 0) {
                CustomHelper::NotifyMultipleUsers($deviceToken->toArray(), 'Ride updated', $message, ['tripId' => $request->rideId]);
            }
            Notification::insert([
                "user_id" => $rideData->driver_id,
                'is_read' => 0,
                'title' => "Your Ride Status is updated",
                'ride_id' => $request->rideId,
                'message' => $message,
                'ride_trip_type' => 1,
                'type' => 1
            ]);
            // send notification end
            return CustomHelper::SuccessWithoutData("Your Ride Updated successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function updateTripDrop(Request $request)
    {
        try {
            TripRequest::where('id', $request->tripId)->update([
                'is_drop_off' => 1
            ]);

            $userData = User::find($request->userId);
            $message = $userData->name . " has marked the trip as completed";
            $driverId = TripBid::where('trip_id', $request->tripId)->whereIn('status', [1, 2, 3])->first();
            // send notification
            if ($driverId) {
                $deviceToken = User::whereIn('id', [$driverId->driver_id])->whereNotNull('device_token')->get()->pluck('device_token');
                if (count($deviceToken) > 0) {
                    CustomHelper::NotifyMultipleUsers($deviceToken->toArray(), 'Ride updated', $message, ['tripId' => $request->tripId]);
                }
                Notification::insert([
                    "user_id" => $driverId->driver_id,
                    'is_read' => 0,
                    'title' => "Your Ride Status is updated",
                    'trip_id' => $request->tripId,
                    'message' => $message,
                    'ride_trip_type' => 2,
                    'type' => 1
                ]);
            }
            // send notification end

            return CustomHelper::SuccessWithoutData("Ride Updated successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
}
