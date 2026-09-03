<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\AdminUser;
use App\Models\AdminPrivilege;

class AdminUsersController extends Controller
{
    function getIndex()
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $request = request();
        $data = [];
        $data['page_title'] = 'Admin Users';
        $data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
        $q = $request->get('q');
        $filter_clumn = (!empty($request->get('filter_column'))) ? $request->get('filter_column') : 'created_at';
        $sorting = (!empty($request->get('sorting'))) ? $request->get('sorting') : 'desc';
        $data['rows'] = AdminUser::when($q, function ($query) use ($q) {
            $query->whereRaw("( name like '%" . $q . "%' or email like '%" . $q . "%' )");
        })->orderBy($filter_clumn, $sorting)->paginate($limit);
        return view('admin.admin_users.index', $data);
    }

    function getAdd()
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $data = [];
        $data['page_title'] = 'Add User';
        $data['privileges'] = AdminPrivilege::where('is_superadmin', 0)->get();
        return view('admin.admin_users.add', $data);
    }

    function postAddSave(Request $request)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $request->validate([
            'name' => 'required|alpha_spaces|max:150',
            'email' => 'required|email|unique:admin_users|max:225',
            'password' => 'required|max:25',
            'privilege' => 'required|numeric'
        ]);
        AdminUser::insert([
            'id_admin_privileges' => $request->input('privilege'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => \Hash::make($request->input('password')),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => AdminHelper::myId(),
            'status' => $request->input('status')
        ]);
        return redirect()->route('getAdminUsers')->withSuccess('User added successfully');
    }

    function getDetail($id)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
    }

    function getEdit($id)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        $data = [];
        $data['page_title'] = 'Edit User';
        $data['privileges'] = AdminPrivilege::where('is_superadmin', 0)->get();
        $data['row'] = AdminUser::find($id);
        return view('admin.admin_users.edit', $data);
    }

    function postUpdateSave($id, Request $request)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }

        $request->validate([
            'name' => 'required|alpha_spaces|max:150',
            'email' => 'required|email|unique:admin_users,email,' . $id . '|max:225',
            'password' => 'max:25|nullable',
            'privilege' => 'required|numeric'
        ]);

        $admin_user = AdminUser::find($id);
        if ($request->input('privilege')) {
            $admin_user->id_admin_privileges = $request->input('privilege');
        }
        $admin_user->name = $request->input('name');
        $admin_user->email = $request->input('email');
        if ($request->input('password')) {
            $admin_user->password = \Hash::make($request->input('password'));
        }
        $admin_user->updated_at = date('Y-m-d H:i:s');
        $admin_user->status = $request->input('status');
        $admin_user->save();

        return redirect()->route('getAdminUsers')->withSuccess('User updated successfully');
    }

    function getDelete($id)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
        AdminUser::where('id', $id)->delete();
        return redirect()->route('getAdminUsers')->withSuccess('User deleted successfully');
    }

    public function postActionSelected(Request $request)
    {
        if (!AdminHelper::isSuperadmin()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }
    }
}
