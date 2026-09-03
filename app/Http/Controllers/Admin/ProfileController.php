<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\Settings;
use App\Models\AdminUser;

class ProfileController extends Controller
{
    function getProfileData()
    {
        $data = [];
        $data['page_title'] = "Update Profile";
        $data['row'] = AdminUser::find(AdminHelper::myId());
        return view('admin.profile.add', $data);
    }

    function postSaveProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'photo' => 'mimes:jpeg,jpg,png|max:2000',
            'email' => 'email|max:255',
        ]);

        $profiledata = AdminUser::find(AdminHelper::myId());
        $profile_image = '';
        $date = date('Y-m-d H:i:s');

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $name = 'img-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/profile/');
            $image->move($destinationPath, $name);
            $profile_image = 'uploads/images/profile/' . $name;
        }

        if (!empty($profiledata)) {
            $profiledata->name = $request->input('name');
            if (!empty($profile_image)) {
                $profiledata->photo = $profile_image;
            }
            $profiledata->email = $request->input('email');
            if (!empty($request->input('password'))) {
                $profiledata->password = \Hash::make($request->input('password'));
            }
            $profiledata->updated_at = $date;
            $profiledata->save();
            $photo = ($profiledata->photo) ? asset('public/' . $profiledata->photo) : asset('admin/images/avatar.jpg');
            Session::put('admin_photo', $photo);
        } else {
            AdminUser::insert([
                'name' => $request->input('name'),
                'photo' => $profile_image,
                'password' => \Hash::make($request->input('password')),
                'created_at' => $date
            ]);
        }

        return redirect()->back()->withSuccess('Admin profile has been updated successfully!');
    }
}
