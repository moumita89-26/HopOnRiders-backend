<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManageBookingController extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Banner Images';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = $request->get('filter_column');
        $sorting = $request->get('sorting');
        if ($filter_clumn != '') {
            $data['rows'] = ManageBanner::Join('cms_pages', 'cms_pages.id', '=', 'manage_banners.page')->when($q, function ($query) use ($q) {
                $query->whereRaw("( `manage_banners`.`name` like '%" . $q . "%')");
            })->orderBy($filter_clumn, $sorting)->select('manage_banners.*')->paginate($limit);
        } else {
            $data['rows'] = ManageBanner::Join('cms_pages', 'cms_pages.id', '=', 'manage_banners.page')->when($q, function ($query) use ($q) {
                $query->whereRaw("( `manage_banners`.`name` like '%" . $q . "%')");
                //$query->orwhereRaw("( `cms_pages`.`page_title` like '%".$q."%')");
            })->orderBy('manage_banners.id', 'desc')->select('manage_banners.*')->paginate($limit);
        }
        //dd($data['rows']);   
        $data['pages'] = CmsPages::get();
        return view('admin.banner.index', $data);
    }

    function addManage()
    {
        $data = [];
        $data['page_title'] = 'Add Banner Image';
        $data['pages'] = CmsPages::where('status', 1)->get();
        return view('admin.banner.add', $data);
    }

    function postAddSave(Request $request)
    {


        $request->validate([
            'name' => 'required|alpha_spaces|max:100',
            //'banner_type' => 'required|max:100',
            //'page' => 'required',
            //'link' => 'url|max:255',
            /*'image' => 'required|image|mimes:jpeg,jpg,png|max:1024|dimensions:min_width=648,min_height=594,max_width:648,max_height:594', */
            'image' => 'required|image|mimes:jpeg,jpg,png|max:1024|dimensions:width=1920,height=462',
            'status' => 'required|numeric'
        ]);

        // $isdata = ManageBanner::where('name' ,$request->input('name'))->where('is_delete','0')->get();

        // if(!$isdata->isEmpty()){
        //    return redirect()->back()->withError('Image with this name under this page already exists!'); 
        // }

        $banner_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'banner-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('admin/uploads/images/banners/');
            $image->move($destinationPath, $name);
            $banner_image = 'admin/uploads/images/banners/' . $name;
        }
        if ($request->input('page') == '') {
            $page_type = 'adimage';
        } else {
            $page_type = 'pageimage';
        }

        ManageBanner::insert([
            'name' => $request->input('name'),
            'page' => $request->input('page'),
            'banner_type' => $page_type,
            //'link' => ($request->input('link') ? $request->input('link'): ''),
            'description' => $request->input('description'),
            'image' => $banner_image,
            'status' => $request->input('status'),
            'created_by' => AdminHelper::myId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath('admin') . '/banner');
        if ($request->input('submit') == 'Save') {
            return redirect($return_url)->withSuccess('Banner image added successfully!');
        } else {
            return redirect()->back()->withSuccess('Banner image added successfully!');
        }
    }

    function getEdit($id)
    {
        $data = [];
        $data['page_title'] = 'Edit Banner Image';
        $data['row'] = ManageBanner::find($id);
        $data['pages'] = CmsPages::where('status', 1)->get();
        return view('admin.banner.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_spaces|max:100',
            //'banner_type' => 'required|max:100',
            //'page' => 'required',
            //'link' => 'url|max:255',
            /* 'image' => 'image|mimes:jpeg,jpg,png|max:1024|dimensions:min_width=648,min_height=594,max_width:648,max_height:594', */
            'image' => 'image|mimes:jpeg,jpg,png|max:1024|dimensions:width=1920,height=462',
            'status' => 'required|numeric'
        ]);

        // $isdata = ManageBanner::where('id' ,'!=',$id)->where('name' ,$request->input('name'))->where('page' ,$request->input('page'))->where('is_delete','0')->get();

        // if(!$isdata->isEmpty()){
        //    return redirect()->back()->withError('Image with this name under this page already exists!'); 
        // }

        $data = ManageBanner::find($id);

        $banner_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'img-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('admin/uploads/images/banners/');
            $image->move($destinationPath, $name);
            $banner_image = 'admin/uploads/images/banners/' . $name;
        }

        $data->name = $request->input('name');
        if (!empty($banner_image)) {

            $destinationPath_delete = public_path('/') . $data->image;

            if (file_exists($destinationPath_delete)) {
                unlink($destinationPath_delete);
            }
            $data->image = $banner_image;
        }
        /* if($request->input('link'))
        {
            $data->link = $request->input('link');    
        }else{
            $data->link = '';
        }*/
        if ($request->input('page') == '') {
            $page_type = 'adimage';
        } else {
            $page_type  = 'pageimage';
        }

        $data->page = $request->input('page');
        $data->banner_type = $page_type;
        $data->description = $request->input('description');
        $data->status = $request->input('status');
        $data->updated_by = AdminHelper::myId();
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath('admin') . '/banner');
        return redirect($return_url)->withSuccess('Banner image updated successfully!');
    }

    function deleteManageBanner($id)
    {
        if (!empty($id)) {
            $data = ManageBanner::find($id);
            $destinationPath_delete = public_path('/') . $data->image;
            if (file_exists($destinationPath_delete) && $data->image != '') {
                unlink($destinationPath_delete);
            }
            ManageBanner::find($id)->delete();
            return redirect()->back()->withSuccess('Banner image deleted successfully!');
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
            ManageBanner::whereIn('id', $id_selected)->update(['is_delete' => 1]);;

            AdminHelper::insertLog("Deleted data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data deleted successfully !";

            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'active') {
            ManageBanner::whereIn('id', $id_selected)->update(['status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);

            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data activated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'inactive') {
            ManageBanner::whereIn('id', $id_selected)->update(['status' => '0', 'updated_at' => date('Y-m-d H:i:s')]);

            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());

            $message = "The selected data inactivated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        return redirect()->back()->withError($message);
    }
}
