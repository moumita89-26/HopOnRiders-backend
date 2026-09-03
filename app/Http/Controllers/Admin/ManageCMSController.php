<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\CmsPages;

class ManageCMSController extends Controller
{
    function getIndex()
    {
        if (!AdminHelper::isView()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $request = request();
        $data = [];
        $data['page_title'] = 'Manage CMS';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = $request->get('filter_column');
        $sorting = $request->get('sorting');
        if ($filter_clumn != '') {
            $data['rows'] = CmsPages::when($q, function ($query) use ($q) {
                $query->whereRaw("( page_title like '%" . $q . "%' or meta_title like '%" . $q . "%' )");
            })->orderBy($filter_clumn, $sorting)->paginate($limit);
        } else {
            $data['rows'] = CmsPages::when($q, function ($query) use ($q) {
                $query->whereRaw("( page_title like '%" . $q . "%' or meta_title like '%" . $q . "%' )");
            })->latest()->paginate($limit);
        }
        return view('admin.cms_management.index', $data);
    }

    function getAdd()
    {
        /*if (!AdminHelper::isCreate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/

        $data = [];
        $data['page_title'] = 'Add CMS';
        $data['locales'] = \Config::get('app.locales');
        return view('admin.cms_management.add', $data);
    }

    function postAddSave(Request $request)
    {
        /* if (!AdminHelper::isCreate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
        //dd($request->all());
        $request->validate([
            'page_title' => 'required|max:150',
            'page_content' => 'nullable|max:10000',
            'meta_title' => 'required|alpha_spaces|max:150',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5000',
            'status' => 'required|numeric'
        ]);
        $isdata = CmsPages::where('page_title', $request->input('page_title'))->get();
        if (!$isdata->isEmpty()) {
            return redirect()->back()->withError('Page Already Exists!');
        }
        $featured_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'featured-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images/cmspage/');
            $image->move($destinationPath, $name);
            $featured_image = 'uploads/images/cmspage/' . $name;
        }
        $slug_val = Str::slug($request->input('page_title'));

        /* foreach(\Config::get('app.locales') as $labbr=>$locale){
            app()->setLocale($labbr);
            $page_title = $request->input('page_title');
            $page_content = $request->input('content');
        }*/

        /*
    	CmsPages::insert([
    		'page_title' => $request->input('page_title'),
    		'page_slug' => $slug_val,
            'page_content' => $page_content,
            'featured_image' => $featured_image,
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),            
            'meta_description' => $request->input('meta_description'),            
    		'status' => $request->input('status'),
            'created_by' => AdminHelper::myId(),
            'user_ip' => $request->ip(),
    		'created_at' => date('Y-m-d H:i:s')
    	]);*/


        $cms = new CmsPages;
        /* foreach(\Config::get('app.locales') as $labbr=>$locale){
            app()->setLocale($labbr);
            $cms->page_title = $request->page_title;
            $cms->page_content = $request->content;
        }*/

        //$cms->page_title = $request->input('page_title');
        $cms->page_slug = $slug_val;

        //$cms->page_content = $request->input('content');
        $content = $request->input('content');
        $content = str_replace('<body id="iz1o">', '', $content);
        $content = str_replace('</body>', '', $content);
        if (!empty($featured_image)) {
            $cms->featured_image = $featured_image;
        }
        $cms->page_title = $request->page_title;
        $cms->page_content = $content;
        $cms->meta_title = $request->input('meta_title');
        $cms->meta_keywords = $request->input('meta_keywords');
        $cms->meta_description = $request->input('meta_description');
        $cms->status = $request->input('status');
        $cms->updated_by = AdminHelper::myId();
        $cms->created_by = AdminHelper::myId();
        $cms->user_ip = $request->ip();
        $cms->updated_at = date('Y-m-d H:i:s');
        $cms->save();
        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath() . '/manage-cms');
        if ($request->input('submit') == 'Save') {
            return redirect($return_url)->withSuccess('CMS added successfully!');
        } else {
            return redirect()->back()->withSuccess('CMS added successfully!');
        }
    }

    function getDetail($id)
    {
        if (!AdminHelper::isRead()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $data = [];
        $data['page_title'] = 'Details CMS';
        $data['row'] = CmsPages::find($id);
        return view('admin.cms_management.detail', $data);
    }

    function getEdit($id)
    {
        /* if (!AdminHelper::isUpdate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
        $data = [];
        $data['page_title'] = 'Edit CMS';
        $data['row'] = CmsPages::find($id);
        $data['locales'] = \Config::get('app.locales');
        return view('admin.cms_management.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {

        // dd($request->all());


        /*if (!AdminHelper::isUpdate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
        $request->validate([
            //'page_title' => 'required|alpha_spaces|max:150',
            'page_title' => 'required|max:150',
            'content' => 'required|string',
            'meta_title' => 'required|alpha_spaces|max:150',
            'image' => 'image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'status' => 'required|numeric'
        ]);

        $isdata = CmsPages::where('id', '!=', $id)->where('page_title', $request->input('page_title'))->get();

        if (!$isdata->isEmpty()) {
            return redirect()->back()->withError('Page Already Exists!');
        }
        $featured_image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = 'featured-image-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/site/cmspage/');
            $image->move($destinationPath, $name);
            $featured_image = 'uploads/site/cmspage/' . $name;
        }
        $cms = CmsPages::find($id);
        if ($cms->meta_title != $request->input('meta_title')) {
            $slug_val = Str::slug($request->input('page_title'));
            $cms->page_slug = $slug_val;
        }


        /* foreach(\Config::get('app.locales') as $labbr=>$locale){
            app()->setLocale($labbr);
            $cms->page_content = $request->input('content');
        }*/




        //$cms->page_title = $request->input('page_title');


        //$cms->page_content = $request->input('content');
        $content = $request->input('content');
        $content = str_replace('<body id="iz1o">', '', $content);
        $content = str_replace('<body>', '', $content);
        $content = str_replace('</body>', '', $content);

        if (!empty($featured_image)) {
            $cms->featured_image = $featured_image;
        }
        $cms->page_content = $content;
        $cms->html_data = $request->input('html_data');
        $cms->meta_title = $request->input('meta_title');
        $cms->meta_keywords = $request->input('meta_keywords');
        $cms->meta_description = $request->input('meta_description');
        $cms->status = $request->input('status');
        $cms->updated_by = AdminHelper::myId();
        $cms->user_ip = $request->ip();
        $cms->updated_at = date('Y-m-d H:i:s');
        $cms->save();
        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath() . '/manage-cms');
        return redirect($return_url)->withSuccess('CMS updated successfully!');
    }

    function getDelete($id)
    {
        if (!AdminHelper::isDelete()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        if (!empty($id)) {
            CmsPages::find($id)->delete();
            return redirect()->back()->withSuccess('CMS deleted successfully!');
        }
    }

    public function postActionSelected(Request $request)
    {
        if (!AdminHelper::isDelete()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $id_selected = $request->input('checkbox');
        $button_name = $request->input('button_name');
        $message = "No action taken";
        if (empty($id_selected)) {
            AdminHelper::redirect($_SERVER['HTTP_REFERER'], 'Please select at least one data!', 'warning');
        }

        if ($button_name == 'delete') {
            CmsPages::whereIn('id', $id_selected)->delete();
            AdminHelper::insertLog("Deleted data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());
            $message = "The selected data deleted successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'active') {
            CmsPages::whereIn('id', $id_selected)->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());
            $message = "The selected data activated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        if ($button_name == 'inactive') {
            CmsPages::whereIn('id', $id_selected)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            AdminHelper::insertLog("Updated data " . implode(',', $id_selected) . " by " . AdminHelper::myName() . " ip: " . $request->ip());
            $message = "The selected data inactivated successfully !";
            return redirect()->back()->withSuccess($message);
        }

        return redirect()->back()->withError($message);
    }
}
