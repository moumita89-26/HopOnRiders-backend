<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function submitReview(Request $request)
    {
        try {
            Rating::insert([
                "trip_id" => $request->tripId,
                "driver_id" => $request->driverId,
                "user_id" => $request->passengerId,
                "rating" => $request->rating,
                "review" => $request->review,
            ]);
            return CustomHelper::SuccessWithoutData("Your Review submitted successfully");
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
}
