<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use App\Helpers\CommonHelper;
use App\Helpers\CustomHelper;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManageUserController extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Manage Users';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $data['rows'] = User::when($q, function ($query) use ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%');
            });
        })
            ->when($request->rating, function ($query) use ($request) {
                $query->whereHas('ratings', function ($q) use ($request) {
                    $q->where('rating', $request->rating);
                });
            })
            ->orderBy($filter_clumn, $sorting)
            ->paginate($limit);

        return view('admin.user.index', $data);
    }
    function getUserReview($id, Request $request)
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Manage Users';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $data['rows'] = Rating::where('user_id', $id)->orderBy($filter_clumn, $sorting)->paginate($limit);
        return view('admin.user.reviews', $data);
    }

    function getAdd()
    {
        $data = [];
        $data['page_title'] = 'Add User';
        // $data['pages'] = CmsPages::where('status',1)->get();
        return view('admin.user.add', $data);
    }

    function postAddSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|string|unique:users|max:250',
            'phone' => 'required|unique:users',
            'password' => 'required',
            'is_verified' => 'required|string|max:16',
            'role' => 'required|numeric',
            'travel_preferences' => 'required|string|max:255',
        ]);

        $validator->sometimes(['vehicle_make', 'vehicle_model', 'vehicle_color', 'registration_number', 'fuel_cost_per_km', 'is_online'], 'required', function ($input) {
            return $input->role == 1;
        });

        $validator->validate();

        $data = new User;
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = 'profile-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/profile/');
            $image->move($destinationPath, $name);
            $data->profile_picture = 'uploads/images/profile/' . $name;
        }
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->phone = $request->input('phone');
        $data->password = $request->input('password');
        $data->role = $request->input('role');
        $data->is_verified = $request->input('is_verified');
        $data->vehicle_make = $request->input('vehicle_make');
        $data->vehicle_model = $request->input('vehicle_model');
        $data->is_document_verify = $request->input('is_document_verify');
        $data->vehicle_color = $request->input('vehicle_color');
        $data->registration_number = $request->input('registration_number');
        $data->fuel_cost_per_km = $request->input('fuel_cost_per_km');
        $data->is_online = $request->input('is_online');
        $data->travel_preferences = $request->input('travel_preferences');
        $data->save();
        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : route('getManageUser'));
        if ($request->input('submit') == 'Save') {
            return redirect($return_url)->withSuccess('User added successfully!');
        } else {
            return redirect()->back()->withSuccess('User added successfully!');
        }
    }

    function getDetail($id)
    {
        $data = [];
        $data['page_title'] = 'Detail User';
        $data['row'] = User::find($id);

        return view('admin.user.detail', $data);
    }

    function getEdit($id)
    {
        $data = [];
        $data['page_title'] = 'Edit User';
        $data['row'] = User::find($id);

        return view('admin.user.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:150',
        //     'email' => ['required', Rule::unique('users')->ignore($id), 'email', 'max:200', 'regex:/(.+)@(.+)\.(.+)/i'],
        //     'phone' => ['required', Rule::unique('users')->ignore($id)],
        //     'role' => 'required',
        //     'is_verified' => 'required|string|max:16',
        //     'is_online' => 'required|numeric',
        // ]);
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => [
                'required',
                Rule::unique('users')->ignore($id),
                'email',
                'max:200',
                'regex:/(.+)@(.+)\.(.+)/i'
            ],
            'phone' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'role' => 'required',
            'is_verified' => 'required|string|max:16',
            'is_online' => 'required|numeric',
        ]);


        $data = User::find($id);
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = 'profile-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/profile/');
            $image->move($destinationPath, $name);
            $data->profile_picture = 'uploads/images/profile/' . $name;
        }
        // $data->name = $request->input('name');
        // $data->email = $request->input('email');
        // $data->phone = $request->input('phone');
        // $data->role = $request->input('role');
        // $data->is_verified = $request->input('is_verified');
        // $data->vehicle_make = $request->input('vehicle_make');
        // $data->vehicle_model = $request->input('vehicle_model');
        // $data->vehicle_color = $request->input('vehicle_color');
        // $data->registration_number = $request->input('registration_number');
        // $data->fuel_cost_per_km = $request->input('fuel_cost_per_km');
        // $data->is_online = $request->input('is_online');
        // $data->travel_preferences = $request->input('travel_preferences');
        if ($request->has('name')) {
            $data->name = $request->input('name');
        }
        if ($request->has('email')) {
            $data->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $data->phone = $request->input('phone');
        }
        if ($request->has('role')) {
            $data->role = $request->input('role');
        }
        if ($request->has('is_verified')) {
            $data->is_verified = $request->input('is_verified');
        }
        if ($request->has('vehicle_make')) {
            $data->vehicle_make = $request->input('vehicle_make');
        }
        if ($request->has('vehicle_model')) {
            $data->vehicle_model = $request->input('vehicle_model');
        }
        if ($request->has('vehicle_color')) {
            $data->vehicle_color = $request->input('vehicle_color');
        }
        if ($request->has('registration_number')) {
            $data->registration_number = $request->input('registration_number');
        }
        if ($request->has('fuel_cost_per_km')) {
            $data->fuel_cost_per_km = $request->input('fuel_cost_per_km');
        }
        if ($request->has('is_online')) {
            $data->is_online = $request->input('is_online');
        }
        if ($request->has('travel_preferences')) {
            $data->travel_preferences = $request->input('travel_preferences');
        }
        if (!empty($request->input('password'))):
            $data->password = Hash::make($request->input('password'));
        endif;

        if ($request->input('is_document_verify') == 1) {
            // send email to user for verification
            $data->role = 1;
            $data->is_document_verify = 1;
            $emailData = array(
                'name' => $request->name
            );
            try {
                CommonHelper::sendEmail(['to' =>  $request->email, 'data' => $emailData, 'template' => 'approve']);
            } catch (\Exception $e) {
                return CustomHelper::ErrorResponse($e->getMessage());
            }
        }
        $data->save();
        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : route('getManageUser'));
        return redirect($return_url)->withSuccess('User updated successfully!');
    }

    function getDelete($id)
    {
        if (!empty($id)) {
            $data = User::find($id);
            $data->delete();
            return redirect()->back()->withSuccess('User deleted successfully!');
        }
    }
    function deleteReview($id)
    {
        if (!empty($id)) {
            $data = Rating::find($id);
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
            User::whereIn('id', $id_selected)->delete();;
            $message = "The selected data deleted successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'active') {
            User::whereIn('id', $id_selected)->update(['status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
            $message = "The selected data activated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'inactive') {
            User::whereIn('id', $id_selected)->update(['status' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
            $message = "The selected data inactivated successfully !";
            return redirect()->back()->withSuccess($message);
        }
        return redirect()->back()->withError($message);
    }

    function updateProfileStatus($id, $status)
    {
        if ($id) {
            User::where('id', $id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->back()->withSuccess('Status updated successfully');
        }
    }
}
