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
use App\Models\CmsEmailTemplate;

class ManageEmailTemplates extends Controller
{
    function getIndex()
    {
        $request = request();
        $data = [];
        $data['page_title'] = 'Email Templates';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = $request->get('filter_column');
        $sorting = $request->get('sorting');
        if ($filter_clumn != '') {
            $data['rows'] = CmsEmailTemplate::where('status', 1)->when($q, function ($query) use ($q) {
                $query->whereRaw("( name like '%" . $q . "%' or subject like '%" . $q . "%' )");
            })->orderBy($filter_clumn, $sorting)->paginate($limit);
        } else {
            $data['rows'] = CmsEmailTemplate::where('status', 1)->when($q, function ($query) use ($q) {
                $query->whereRaw("( name like '%" . $q . "%' or subject like '%" . $q . "%' )");
            })->latest()->paginate($limit);
        }
        return view('admin.email_tempate_manage.index', $data);
    }

    function getAdd()
    {
        $data = [];
        $data['page_title'] = 'Add Email Template';

        return view('admin.email_tempate_manage.add', $data);
    }

    function postAddSave(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_spaces|max:150',
            'slug' => 'required|max:150',
            'subject' => 'required|max:150',
            'content' => 'required',
            'description' => 'required',
            'from_name' => 'required|max:150',
            'from_email' => 'required|email',
            'cc_email' => 'nullable|email'
        ]);

        $isdata = CmsEmailTemplate::where('slug', $request->input('slug'))->get();
        if (!$isdata->isEmpty()) {
            return redirect()->back()->withError('Email Template Already Exists!');
        }


        CmsEmailTemplate::insert([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'subject' => $request->input('subject'),
            'content' =>  $request->input('content'),
            'description' => $request->input('description'),
            'from_name' => $request->input('from_name'),
            'from_email' => $request->input('from_email'),
            'cc_email' => $request->input('cc_email'),
            'created_by' => AdminHelper::myId(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath() . '/email-templates');
        if ($request->input('submit') == 'Save') {
            return redirect($return_url)->withSuccess('Email template added successfully!');
        } else {
            return redirect()->back()->withSuccess('Email template added successfully!');
        }
    }

    function getDetail($id)
    {
        $data = [];
        $data['page_title'] = 'Details Email Template';
        $data['row'] = CmsEmailTemplate::find($id);

        return view('admin.email_tempate_manage.detail', $data);
    }

    function getEdit($id)
    {
        $data = [];
        $data['page_title'] = 'Edit Email Template';
        $data['row'] = CmsEmailTemplate::find($id);

        return view('admin.email_tempate_manage.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {

        $request->validate([
            'name' => 'required|alpha_spaces|max:150',
            'slug' => 'required|max:150',
            'subject' => 'required|max:150',
            'content' => 'required',
            'description' => 'required',
            'from_name' => 'required|max:150',
            'from_email' => 'required|email',
            'cc_email' => 'nullable|email'
        ]);

        $isdata = CmsEmailTemplate::where('id', '!=', $id)->where('slug', $request->input('slug'))->get();

        if (!$isdata->isEmpty()) {
            return redirect()->back()->withError('Email Template Already Exists!');
        }

        $cms = CmsEmailTemplate::find($id);
        $cms->name = $request->input('name');
        $cms->slug = $request->input('slug');
        $cms->subject = $request->input('subject');
        $cms->content = $request->input('content');;
        $cms->description = $request->input('description');
        $cms->from_name = $request->input('from_name');
        $cms->from_email = $request->input('from_email');
        $cms->cc_email = $request->input('cc_email');
        $cms->updated_by = AdminHelper::myId();
        $cms->updated_at = date('Y-m-d H:i:s');
        $cms->save();
        $return_url = (!empty($request->input('return_url')) ? $request->input('return_url') : AdminHelper::adminPath() . '/email-templates');
        return redirect($return_url)->withSuccess('Email Template updated successfully!');
    }

    function getDelete($id)
    {
        if (!empty($id)) {
            CmsPages::find($id)->delete();
            return redirect()->back()->withSuccess('CMS deleted successfully!');
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
