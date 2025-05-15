<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'dashboardAdmin',
            'ManageRolesPermissions',
            'ManageActivity',
            'ManageEmployee',
            'dashboardHR',
            'ManagePayrolls',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $role = Role::create(['name' => 'Admin']);
        $role->givePermissionTo([
            'ManageActivity',
            'ManageRolesPermissions',
            'dashboardAdmin',
            
        ]);

        $role = Role::create(['name' => 'HeadHR']);
        $role->givePermissionTo([
            'ManageEmployee',
            'dashboardHR',
            'ManagePayrolls',
            
        ]);

        
    }
    
}