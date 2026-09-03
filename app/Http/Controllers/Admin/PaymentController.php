<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ride;
use App\Models\TripRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Manage Report';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $data['rides'] = Ride::when($q, function ($query) use ($q) {
            $query->whereRaw("( name like '%" . $q . "%' or email like '%" . $q . "%' or phone_number like '%" . $q . "%')");
        })->orderBy($filter_clumn, $sorting)->paginate($limit);
        $data['trips'] = TripRequest::when($q, function ($query) use ($q) {
            $query->whereRaw("( name like '%" . $q . "%' or email like '%" . $q . "%' or phone_number like '%" . $q . "%')");
        })->orderBy($filter_clumn, $sorting)->paginate($limit);
        return view('admin.report.index', $data);
    }
}
