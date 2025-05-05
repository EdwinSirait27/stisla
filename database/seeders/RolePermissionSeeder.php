<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder

{

    public function run()
    

    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Role::truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permissions
        $permissions = [
            'dashboardAdmin',
            'dashboardHR',
            'ManageActivity',
            'ManageRoles',
            'ManageUser',
        ];
     
        $permissionIds = [];
foreach ($permissions as $permission) {
    $perm = Permission::updateOrCreate(
        ['name' => $permission], // cari berdasarkan name
        [
            'id' => Uuid::uuid7()->toString(), // dipakai jika membuat baru
            'guard_name' => 'web'
        ]
    );
    $permissionIds[] = $perm->id;
}


        // Buat roles dengan UUID konsisten
        $adminRole = Role::updateOrCreate(
            ['name' => 'Admin'],
            [
                'id' => Uuid::uuid7()->toString(),
                'guard_name' => 'web'
            ]
        );
        $adminRole->syncPermissions($permissionIds);

        $hrRole = Role::updateOrCreate(
            ['name' => 'HeadHR'],
            [
                'id' => Uuid::uuid7()->toString(),
                'guard_name' => 'web'
            ]
        );
        $hrRole->syncPermissions($permissionIds);

        // Editor role (jika diperlukan)
        $editorRole = Role::updateOrCreate(
            ['name' => 'editor'],
            [
                'id' => Uuid::uuid7()->toString(),
                'guard_name' => 'web'
            ]
        );
        $editorRole->syncPermissions(['dashboardAdmin']);

        // User role (jika diperlukan)
        $userRole = Role::updateOrCreate(
            ['name' => 'user'],
            [
                'id' => Uuid::uuid7()->toString(),
                'guard_name' => 'web'
            ]
        );
        $userRole->syncPermissions(['dashboardAdmin']);

        // Assign roles ke user yang sudah ada
        if ($adminUser = User::where('username', '20250400001')->first()) {
            $adminUser->syncRoles('Admin');
        }

        if ($hrUser = User::where('username', '20250400002')->first()) {
            $hrUser->syncRoles('HeadHR');
        }
    }
}