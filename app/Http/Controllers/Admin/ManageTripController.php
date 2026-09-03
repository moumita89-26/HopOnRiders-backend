<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageTripController extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Manage Rides';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $data['rows'] = Ride::when($q, function ($query) use ($q) {
            $query->whereRaw("( name like '%" . $q . "%' or email like '%" . $q . "%' or phone_number like '%" . $q . "%')");
        })->orderBy($filter_clumn, $sorting)->paginate($limit);
        return view('admin.rides.index', $data);
    }

    function getAdd()
    {
        $data = [];
        $data['page_title'] = 'Add User';
        // $data['pages'] = CmsPages::where('status',1)->get();
        return view('admin.rides.add', $data);
    }

    function postAddSave(Request $request) {}

    function getDetail($id)
    {
        $data = [];
        $data['page_title'] = 'Detail User';
        $data['row'] = $tripData = Ride::with([
            'wayPoint',
            'booking' => function ($query) {
                $query->select('trip_id', 'passenger_id', 'total_fare', DB::raw('SUM(seats_booked) as total_seats'))
                    ->groupBy('trip_id', 'passenger_id', 'total_fare');
            }
        ])->find($id);
        return view('admin.rides.detail', $data);
    }

    function getEdit($id)
    {
        $data = [];
        $data['page_title'] = 'Edit User';
        $data['row'] = Ride::find($id);

        return view('admin.rides.edit', $data);
    }

    function postUpdateSave($id, Request $request) {}

    function getDelete($id)
    {
        if (!empty($id)) {
            $data = Ride::find($id);
            $data->delete();
            return redirect()->back()->withSuccess('User deleted successfully!');
        }
    }

    public function postActionSelected(Request $request)
    {
        $id_selected = $request->input('checkbox');
        $button_name = $request->input('button_name');
        $message = "No action taken";
        if (empty($id_selected)) {
            AdminHelper::redirect($_SERVER['HTTP_REFERER'], 'Please select at least one data!', 'warning');
        }

        if ($button_name == 'delete') {
            Ride::whereIn('id', $id_selected)->delete();;
            $message = "The selected data deleted successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'active') {
            Ride::whereIn('id', $id_selected)->update(['status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
            $message = "The selected data activated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'inactive') {
            Ride::whereIn('id', $id_selected)->update(['status' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
            $message = "The selected data inactivated successfully !";
            return redirect()->back()->withSuccess($message);
        }
        return redirect()->back()->withError($message);
    }

    function updateProfileStatus($id, $status)
    {
        if ($id) {
            Ride::where('id', $id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->back()->withSuccess('Status updated successfully');
        }
    }
}
