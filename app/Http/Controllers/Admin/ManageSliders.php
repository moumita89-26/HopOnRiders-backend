<?php

namespace App\Http\Controllers\Admin;

use AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\CmsPages;
use App\Models\HomepageSlider;

class ManageSliders extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Slider Images';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column')) ? $request->get('filter_column') : 'created_at');
        $sorting = (!empty($request->get('sorting')) ? $request->get('sorting') : 'desc');
        $data['rows'] = HomepageSlider::select('homepage_sliders.*', 'master_languages.title as language_title')->join('master_languages', 'master_languages.lang_code', 'homepage_sliders.language')->when($q, function ($query) use ($q) {
            $query->whereRaw("( homepage_sliders.title like '%" . $q . "%' or `short_description` like '%" . $q . "%')");
        })->orderBy($filter_clumn, $sorting)->paginate($limit);
        return view('admin.slider.index', $data);
    }

    function addManage()
    {
        $data = [];
        $data['page_title'] = 'Add Slider Image';
        $data['languages'] = DB::table('master_languages')->get();
        return view('admin.slider.add', $data);
    }

    function postAddSave(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'short_description' => 'required|string|max:300',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2024',
            'language' => 'required|string|max:5',
            'status' => 'required|numeric'
        ]);

        $slider_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'slider-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/sliders/');
            $image->move($destinationPath, $name);
            $slider_image = 'uploads/images/sliders/' . $name;
        }

        HomepageSlider::insert([
            'title' => $request->input('title'),
            'short_description' => $request->input('short_description'),
            'image' => $slider_image,
            'status' => $request->input('status'),
            'language' => $request->input('language'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath('admin') . '/slider');
        if ($request->input('submit') == 'Save') {
            return redirect($return_url)->withSuccess('Slider image added successfully!');
        } else {
            return redirect()->back()->withSuccess('Slider image added successfully!');
        }
    }

    function getEdit($id)
    {
        $data = [];
        $data['page_title'] = 'Edit Slider Image';
        $data['row'] = HomepageSlider::find($id);
        $data['languages'] = DB::table('master_languages')->get();
        return view('admin.slider.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'short_description' => 'required|string|max:300',
            'image' => 'image|mimes:jpeg,jpg,png|max:2024',
            'language' => 'required|string|max:5',
            'status' => 'required|numeric'
        ]);

        $data = HomepageSlider::find($id);

        $slider_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'slider-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/sliders/');
            $image->move($destinationPath, $name);
            $slider_image = 'uploads/images/sliders/' . $name;
        }

        $data->title = $request->input('title');
        $data->short_description = $request->input('short_description');
        if (!empty($slider_image)) {
            $data->image = $slider_image;
        }

        $data->status = $request->input('status');
        $data->language = $request->input('language');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath('admin') . '/slider');
        return redirect($return_url)->withSuccess('Slider image updated successfully!');
    }

    function getDetail($id)
    {
        if (!AdminHelper::isRead()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $data = [];
        $data['page_title'] = 'Slider Image Details';
        $data['row'] = HomepageSlider::find($id);

        return view('admin.slider.detail', $data);
    }

    function deleteManageSlider($id)
    {
        if (!empty($id)) {
            $count_banner =  HomepageSlider::count();
            if ($count_banner > 1) {
                $data = HomepageSlider::find($id);
                $destinationPath_delete = public_path('/') . $data->image;
                if (file_exists($destinationPath_delete) && $data->image != '') {
                    unlink($destinationPath_delete);
                }
                HomepageSlider::where('id', $id)->delete();
                return redirect()->back()->withSuccess('Slider image deleted successfully!');
            } else {
                return redirect()->back()->withError('Minimum one image needed!');
            }
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

            foreach ($id_selected as $key => $ids) {
                $data = HomepageSlider::find($ids);
                $destinationPath_delete = public_path('/') . $data->image;

                if (file_exists($destinationPath_delete)) {
                    unlink($destinationPath_delete);
                }
            }
            HomepageSlider::whereIn('id', $id_selected)->delete();

            AdminHelper::insertLog("Deleted data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data deleted successfully !";

            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'active') {
            HomepageSlider::whereIn('id', $id_selected)->update(['status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);

            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data activated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'inactive') {
            HomepageSlider::whereIn('id', $id_selected)->update(['status' => '0', 'updated_at' => date('Y-m-d H:i:s')]);

            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data inactivated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        return redirect()->back()->withError($message);
    }
}
