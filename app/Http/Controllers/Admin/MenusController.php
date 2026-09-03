<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\Menus;
use App\Models\AdminPrivilege;
use App\Models\AdminPrivilegeRole;

class MenusController extends Controller
{
	function getIndex()
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$data = [];
		$data['page_title'] = "Menu Management";
		$data['rows'] = Menus::where('parent_id', 0)->orderBy('sorting', 'asc')->get();
		$data['parent_menus'] = Menus::where('parent_id', 0)->orderBy('name', 'asc')->get();
		$data['last_sort'] = Menus::max('sorting');
		return view('admin.menus_management', $data);
	}

	function editMenu($id)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$data = [];
		$data['page_title'] = "Menu Management";
		$data['rows'] = Menus::where('parent_id', 0)->orderBy('sorting', 'asc')->get();
		$data['parent_menus'] = Menus::where('parent_id', 0)->orderBy('name', 'asc')->get();
		$data['last_sort'] = Menus::max('sorting');
		$data['menu_item'] = Menus::find($id);
		return view('admin.menus_management', $data);
	}

	function postAddSave(Request $request)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request->validate([
			'parent_id' => 'numeric|nullable',
			'menu_name' => 'required|string|alpha_spaces|max:50|unique:admin_menus,name',
			'type' => 'required',
			'menu_path' => 'required',
			'sorting' => 'numeric|min:1',
			'status' => 'numeric|min:0'
		]);

		Menus::insert([
			'name' => $request->input('menu_name'),
			'type' => $request->input('type'),
			'path' => $request->input('menu_path'),
			'icon' => $request->input('icon'),
			'parent_id' => (!empty($request->input('parent_id')) ? $request->input('parent_id') : 0),
			'is_active' => $request->input('status'),
			'sorting' => $request->input('sorting'),
			'sql_query' => $request->input('sql_query'),
			'created_at' => date('Y-m-d H:i:s')
		]);

		return redirect()->back()->withSuccess('Menu added successfully');
	}

	function postUpdateSave($id, Request $request)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request->validate([
			'parent_id' => 'numeric|nullable',
			'menu_name' => 'required|string|alpha_spaces|max:50|unique:admin_menus,name,' . $id,
			'type' => 'required',
			'menu_path' => 'required',
			'sorting' => 'numeric|min:1',
			'status' => 'numeric|min:0'
		]);

		Menus::where('id', $id)->update([
			'name' => $request->input('menu_name'),
			'type' => $request->input('type'),
			'path' => $request->input('menu_path'),
			'icon' => $request->input('icon'),
			'parent_id' => (!empty($request->input('parent_id')) ? $request->input('parent_id') : 0),
			'is_active' => $request->input('status'),
			'sorting' => $request->input('sorting'),
			'sql_query' => $request->input('sql_query'),
			'updated_at' => date('Y-m-d H:i:s')
		]);

		return redirect()->route('getMenus')->withSuccess('Menu updated successfully');
	}

	function deleteMenu($id)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		if ($id) {
			Menus::where('id', $id)->delete();
			AdminPrivilegeRole::where('id_admin_menus', $id)->delete();
			return redirect()->back()->withSuccess('Menu deleted successfully');
		}
	}

	//Privileges
	function getPrivilege()
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request = request();
		$data = [];
		$data['page_title'] = "Privileges";
		$data['limit'] = $limit = (!empty($request->get('limit')) ? $request->get('limit') : 20);
		//dd($limit);
		$q = $request->get('q');
		$sorting = $request->get('sorting');
		$data['rows'] = AdminPrivilege::paginate(15);

		return view('admin.admin_privileges.index', $data);
	}

	function getAddPrivilege()
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request = request();
		$data = [];
		$data['page_title'] = "Add Privilege";
		$data['menus'] = Menus::orderBy('parent_id', 'asc')->orderBy('sorting', 'asc')->get();

		return view('admin.admin_privileges.add', $data);
	}

	function postAddPrivilege(Request $request)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request->validate([
			'name' => 'required|string|alpha_spaces|max:50|unique:admin_privileges,name',
		]);

		$id = AdminPrivilege::insertGetId([
			'name' => $request->input('name'),
			'is_superadmin' => 0,
			'created_by' => AdminHelper::myId(),
			'user_ip' => $request->ip(),
			'created_at' => date('Y-m-d H:i:s')
		]);

		$priv = $request->input("privileges");
		if (!empty($priv)) {
			foreach ($priv as $id_modul => $data) {
				$arrs = [];
				$arrs['is_visible'] = @$data['is_visible'] ?: 0;
				$arrs['is_create'] = @$data['is_create'] ?: 0;
				$arrs['is_read'] = @$data['is_read'] ?: 0;
				$arrs['is_edit'] = @$data['is_edit'] ?: 0;
				$arrs['is_delete'] = @$data['is_delete'] ?: 0;
				$arrs['id_admin_privileges'] = $id;
				$arrs['id_admin_menus'] = $id_modul;
				$arrs['created_by'] = AdminHelper::myId();
				$arrs['created_at'] = date('Y-m-d H:i:s');
				AdminPrivilegeRole::insert($arrs);
			}
		}

		//Refresh Session Roles
		$roles = DB::table('admin_privileges_roles')->where('id_admin_privileges', AdminHelper::myPrivilegeId())->join('admin_menus', 'admin_menus.id', '=', 'id_admin_menus')->select('admin_menus.name', 'admin_menus.path', 'admin_menus.type', 'is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete')->get();
		Session::put('admin_privileges_roles', $roles);

		return redirect()->route('getPrivilege')->withSuccess('Privilege added successfully');
	}

	function getEditPrivilege($id)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request = request();
		$data = [];
		$data['page_title'] = "Edit Privilege";
		$data['menus'] = Menus::orderBy('parent_id', 'asc')->orderBy('sorting', 'asc')->get();
		$data['row'] = AdminPrivilege::find($id);

		return view('admin.admin_privileges.edit', $data);
	}

	function postUpdatePrivilege($id, Request $request)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$request->validate([
			'name' => 'required|string|alpha_spaces|max:50|unique:admin_privileges,name,' . $id,
		]);

		AdminPrivilege::where('id', $id)->update([
			'name' => $request->input('name'),
			'updated_by' => AdminHelper::myId(),
			'user_ip' => $request->ip(),
			'updated_at' => date('Y-m-d H:i:s')
		]);
		AdminPrivilegeRole::where('id_admin_privileges', $id)->delete();

		$priv = $request->input("privileges");

		if (!empty($priv)) {
			foreach ($priv as $id_modul => $data) {
				$arrs = [];
				$arrs['is_visible'] = @$data['is_visible'] ?: 0;
				$arrs['is_create'] = @$data['is_create'] ?: 0;
				$arrs['is_read'] = @$data['is_read'] ?: 0;
				$arrs['is_edit'] = @$data['is_edit'] ?: 0;
				$arrs['is_delete'] = @$data['is_delete'] ?: 0;
				$arrs['id_admin_privileges'] = $id;
				$arrs['id_admin_menus'] = $id_modul;
				$arrs['created_by'] = AdminHelper::myId();
				$arrs['created_at'] = date('Y-m-d H:i:s');
				AdminPrivilegeRole::insert($arrs);
			}
		}

		//Refresh Session Roles
		if ($id == AdminHelper::myPrivilegeId()) {
			$roles = DB::table('admin_privileges_roles')->where('id_admin_privileges', AdminHelper::myPrivilegeId())->join('admin_menus', 'admin_menus.id', '=', 'id_admin_menus')->select('admin_menus.name', 'admin_menus.path', 'admin_menus.type', 'is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete')->get();
			Session::put('admin_privileges_roles', $roles);
		}

		return redirect()->route('getPrivilege')->withSuccess('Privilege updated successfully');
	}

	function getDeletePrivilege($id)
	{
		if (!AdminHelper::isSuperadmin()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		if ($id) {
			AdminPrivilege::where('id', $id)->delete();
			AdminPrivilegeRole::where('id_admin_privileges', $id)->delete();

			return redirect()->route('getPrivilege')->withSuccess('Privilege deleted successfully');
		}
	}
}
