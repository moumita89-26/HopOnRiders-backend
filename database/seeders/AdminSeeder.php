<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $privilege_id = DB::table('admin_privileges')->insertGetId([
            'name' => 'Super Admin',
            'is_superadmin' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'user_ip' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('admin_privileges_roles')->insert([            
            'id_admin_privileges' => $privilege_id,
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'user_ip' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $admin_id = DB::table('admin_users')->insertGetId([
            'name' => 'Administrator',
            'photo' => '',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('nopass'),
            'id_admin_privileges' => $privilege_id,
            'created_by' => 1,
            'updated_by' => 1,
            'user_ip' => NULL,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('admin_settings')->insert([
            'appname' => 'Laravel 11',
            'site_email' => 'site_email@email.com',
            'created_by' => $admin_id,
            'updated_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $cms_id = DB::table('admin_menus')->insertGetId([
            'name' => 'Manage CMS',
            'type' => 'Route',
            'path' => 'getManageCMS',
            'icon' => 'fa fa-th-list',
            'parent_id' => 0,
            'is_active' => 1,
            'sorting' => 1,
            'sql_query' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $email_temp_id = DB::table('admin_menus')->insertGetId([
            'name' => 'Manage Email Templates',
            'type' => 'Route',
            'path' => 'getIndexEmailTemplate',
            'icon' => 'fa fa-envelope',
            'parent_id' => 0,
            'is_active' => 1,
            'sorting' => 2,
            'sql_query' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $parent_id = DB::table('admin_menus')->insertGetId([
            'name' => 'Settings',
            'type' => 'URL',
            'path' => '#',
            'icon' => 'ri-settings-2-line',
            'parent_id' => 0,
            'is_active' => 1,
            'sorting' => 3,
            'sql_query' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $setting_id = DB::table('admin_menus')->insertGetId([
            'name' => 'General Settings',
            'type' => 'Route',
            'path' => 'getGeneralSettings',
            'icon' => NULL,
            'parent_id' => $parent_id,
            'is_active' => 1,
            'sorting' => 1,
            'sql_query' => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ]);       

        DB::table('admin_privileges_roles')->insert([
            'id_admin_privileges'=> $privilege_id,
            'id_admin_menus' => $cms_id,
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 0,
            'created_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('admin_privileges_roles')->insert([
            'id_admin_privileges'=> $privilege_id,
            'id_admin_menus' => $email_temp_id,
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 1,
            'created_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('admin_privileges_roles')->insert([
            'id_admin_privileges'=> $privilege_id,
            'id_admin_menus' => $parent_id,
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 0,
            'created_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('admin_privileges_roles')->insert([
            'id_admin_privileges'=> $privilege_id,
            'id_admin_menus' => $setting_id,
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 0,
            'created_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
