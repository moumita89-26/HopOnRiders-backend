<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;

class HopOnAdminMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Manage Users', 'path' => 'getManageUser', 'icon' => 'fa fa-users'],
            ['name' => 'Manage Rides', 'path' => 'getManageRide', 'icon' => 'fa fa-motorcycle'],
            ['name' => 'Manage Trips', 'path' => 'getManageTrip', 'icon' => 'fa fa-motorcycle'],
            ['name' => 'Safety Incident', 'path' => 'getSafetyIncident', 'icon' => 'fa fa-shield'],
            ['name' => 'Reports', 'path' => 'getManageReport', 'icon' => 'fa fa-th-list'],
        ];

        foreach ($menus as $menu) {
            if (! Route::has($menu['path'])) {
                throw new RuntimeException('Missing admin menu route: '.$menu['path']);
            }
        }

        DB::transaction(function () use ($menus) {
            $privileges = DB::table('admin_privileges')->where('is_superadmin', 1)->get();
            if ($privileges->isEmpty()) {
                throw new RuntimeException('Create a Super Admin privilege before seeding HopOn menus.');
            }

            $sorting = (int) DB::table('admin_menus')->where('parent_id', 0)->max('sorting');
            foreach ($menus as $menu) {
                // Match the route, not the label: production labels may differ.
                $menuId = DB::table('admin_menus')->where('type', 'Route')
                    ->where('path', $menu['path'])->value('id');
                if ($menuId === null) {
                    $menuId = DB::table('admin_menus')->insertGetId($menu + [
                        'type' => 'Route', 'parent_id' => 0, 'is_active' => 1,
                        'sorting' => ++$sorting, 'created_at' => now(),
                    ]);
                }

                foreach ($privileges as $privilege) {
                    $role = DB::table('admin_privileges_roles')
                        ->where('id_admin_privileges', $privilege->id)
                        ->where('id_admin_menus', $menuId);
                    if (! $role->exists()) {
                        DB::table('admin_privileges_roles')->insert([
                            'id_admin_privileges' => $privilege->id,
                            'id_admin_menus' => $menuId,
                            'is_visible' => 1, 'is_read' => 1,
                            'is_create' => 1, 'is_edit' => 1, 'is_delete' => 1,
                            'created_by' => $privilege->created_by,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
