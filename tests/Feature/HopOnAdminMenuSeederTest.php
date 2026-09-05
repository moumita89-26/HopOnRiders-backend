<?php

namespace Tests\Feature;

use App\Helpers\AdminHelper;
use Database\Seeders\AdminSeeder;
use Database\Seeders\HopOnAdminMenuSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HopOnAdminMenuSeederTest extends TestCase
{
    private const PATHS = ['getManageUser', 'getManageRide', 'getManageTrip', 'getSafetyIncident', 'getManageReport'];

    protected function setUp(): void
    {
        parent::setUp();

        if ($socket = getenv('HOPON_MENU_TEST_MYSQL_SOCKET')) {
            config()->set('database.default', 'mysql');
            config()->set('database.connections.mysql', [
                'driver' => 'mysql', 'unix_socket' => $socket, 'database' => 'hopon_menu_test',
                'username' => 'root', 'password' => '', 'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
            ]);
            DB::purge('mysql');
        } else {
            if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
                $this->markTestSkipped('Requires pdo_sqlite or an isolated HOPON_MENU_TEST_MYSQL_SOCKET.');
            }
            config()->set('database.default', 'sqlite');
            config()->set('database.connections.sqlite.database', ':memory:');
            DB::purge('sqlite');
        }

        // Use the repository's actual admin schema on an isolated database.
        foreach (['admin_privileges_roles', 'admin_users', 'admin_settings', 'admin_privileges', 'admin_menus'] as $table) {
            Schema::dropIfExists($table);
        }
        foreach ([
            '2022_08_04_092124_create_admin_privileges_table.php' => 'CreateAdminPrivilegesTable',
            '2022_08_04_092144_create_admin_privileges_roles_table.php' => 'CreateAdminPrivilegesRolesTable',
            '2022_08_04_092257_create_admin_users_table.php' => 'CreateAdminUsersTable',
            '2022_08_04_100101_create_admin_settings_table.php' => 'CreateAdminSettingsTable',
        ] as $file => $class) {
            require_once database_path('migrations/'.$file);
            (new $class)->up();
        }
        (require database_path('migrations/2023_11_23_110856_create_admin_menus_table.php'))->up();
    }

    public function test_admin_seed_provides_working_business_sidebar_links(): void
    {
        $this->seed(AdminSeeder::class);
        $privilege = DB::table('admin_privileges')->where('is_superadmin', 1)->value('id');
        session()->put('admin_privileges', $privilege);
        $sidebar = collect(AdminHelper::sidebarMenu())->keyBy('path');

        foreach (self::PATHS as $path) {
            $this->assertTrue($sidebar->has($path), 'Missing sidebar route '.$path);
            $this->assertFalse($sidebar[$path]->is_broken);
            $this->assertSame(route($path), $sidebar[$path]->url);
            $this->assertDatabaseHas('admin_privileges_roles', [
                'id_admin_privileges' => $privilege, 'id_admin_menus' => $sidebar[$path]->id,
                'is_visible' => 1, 'is_read' => 1,
            ]);
        }
    }

    public function test_rerunning_preserves_custom_menus_permissions_and_accounts(): void
    {
        $this->seed(AdminSeeder::class);
        $menu = DB::table('admin_menus')->where('path', 'getSafetyIncident')->value('id');
        DB::table('admin_menus')->where('id', $menu)->update(['name' => 'Custom Safety', 'is_active' => 0, 'sorting' => 42]);
        DB::table('admin_privileges_roles')->where('id_admin_menus', $menu)->update(['is_delete' => 0]);
        $before = [];
        foreach (['admin_menus', 'admin_privileges_roles', 'admin_users', 'admin_settings'] as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->seed(HopOnAdminMenuSeeder::class);
        $this->seed(HopOnAdminMenuSeeder::class);
        foreach ($before as $table => $rows) {
            $this->assertSame($rows, DB::table($table)->orderBy('id')->get()->toJson());
        }
    }

    public function test_existing_installation_gets_missing_links_without_granting_other_roles_access(): void
    {
        $this->seed(AdminSeeder::class);
        $menuId = DB::table('admin_menus')->where('path', 'getManageTrip')->value('id');
        DB::table('admin_privileges_roles')->where('id_admin_menus', $menuId)->delete();
        DB::table('admin_menus')->where('id', $menuId)->delete();
        $other = DB::table('admin_privileges')->insertGetId([
            'name' => 'Support', 'is_superadmin' => 0, 'created_by' => 1,
        ]);

        $this->seed(HopOnAdminMenuSeeder::class);
        $this->assertSame(1, DB::table('admin_menus')->where('path', 'getManageTrip')->count());
        $this->assertSame(0, DB::table('admin_privileges_roles')->where('id_admin_privileges', $other)->count());
        $this->assertDatabaseCount('admin_menus', 9);
    }
}
