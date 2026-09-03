<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Ride;
use App\Models\TripRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Revenue Report';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        // dd($fromDate, $toDate);
        $data['rides'] = Booking::with(['trip', 'user'])
            ->select(
                'passenger_id',
                'trip_id',
                DB::raw('MAX(bookings.status) as status'),
                DB::raw('MAX(total_fare) as total_fare'),
                DB::raw('MAX(booking_fee) as booking_fee'),
                DB::raw('SUM(seats_booked) as total_seats')
            )
            ->join('rides', 'bookings.trip_id', '=', 'rides.id')
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('rides.departure_time', [$fromDate, $toDate]);
            })
            ->groupBy('passenger_id', 'trip_id')
            ->paginate($limit);
        $data['trips'] = TripRequest::with(['bid' => function ($query) {
            $query->whereIn('trip_bids.status', [1, 2, 3]);
        }])
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->when($q, function ($query) use ($q) {
                $query->whereRaw("( name like '%" . $q . "%' or email like '%" . $q . "%' or phone_number like '%" . $q . "%')");
            })->orderBy($filter_clumn, $sorting)->where('status', 3)->paginate($limit);
        return view('admin.report.index', $data);
    }

    public function updatePayoutTrip($id)
    {
        TripRequest::where('id', $id)->update(['payout_status' => 1]);
        return redirect()->back()->withSuccess('Payout successfully');
    }

    public function updateRefundsTrip($id)
    {
        TripRequest::where('id', $id)->update(['refund_status' => 1]);
        return redirect()->back()->withSuccess('Refund successfully');
    }

    public function updatePayoutRide($id)
    {
        Ride::where('id', $id)->update(['payout_status' => 1]);
        return redirect()->back()->withSuccess('Payout successfully');
    }

    public function updateRefundsRide($id)
    {
        Ride::where('id', $id)->update(['refund_status' => 1]);
        return redirect()->back()->withSuccess('Refund successfully');
    }
}
