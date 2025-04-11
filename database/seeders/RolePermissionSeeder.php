<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create permissions (jika belum ada)
    Permission::firstOrCreate(['name' => 'dashboardAdmin']);
    Permission::firstOrCreate(['name' => 'ManageUser']);
    Permission::firstOrCreate(['name' => 'ManageActivity']);
    Permission::firstOrCreate(['name' => 'ManageRoles']);
    Permission::firstOrCreate(['name' => 'dashboardHR']);
    Permission::firstOrCreate(['name' => 'ManageUser']);
    Permission::firstOrCreate(['name' => 'ManageActivity']);

   
    
    $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
    $roleAdmin->givePermissionTo(Permission::all());

    // // Assign role to existing users
    // // Cara 1: By ID
    // $user1 = User::find(); // User dengan ID 1
    // if ($user1) {
    //     $user1->assignRole('Admin');
    // }

    // Cara 2: By Email
    $user = User::where('username', 'edwinsirait')->first();
    if ($user) {
        $user->assignRole('Admin');
    }

    // Cara 3: Assign ke beberapa user sekaligus
    // $users = User::whereIn('id', [3, 4, 5])->get();
    // foreach ($users as $user) {
    //     $user->assignRole('HeadHR');
    // }
    }
}






// public function run()
// {
//     // Reset cached roles and permissions
//     app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

//     // Create permissions (jika belum ada)
//     Permission::firstOrCreate(['name' => 'dashboardAdmin']);
//     Permission::firstOrCreate(['name' => 'dashboardHR']);
//     Permission::firstOrCreate(['name' => 'ManageUser']);
//     Permission::firstOrCreate(['name' => 'ManageActivity']);
//     Permission::firstOrCreate(['name' => 'ManageRoles']);

//     // Create roles (jika belum ada)
//     $roleHeadHR = Role::firstOrCreate(['name' => 'HeadHR']);
//     $roleHeadHR->givePermissionTo(['dashboardHR']);
    
//     $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
//     $roleAdmin->givePermissionTo(Permission::all());

//     // Assign role to existing users
//     // Cara 1: By ID
//     $user1 = User::find(1); // User dengan ID 1
//     if ($user1) {
//         $user1->assignRole('Admin');
//     }

//     // Cara 2: By Email
//     $user2 = User::where('email', 'hr@example.com')->first();
//     if ($user2) {
//         $user2->assignRole('HeadHR');
//     }

//     // Cara 3: Assign ke beberapa user sekaligus
//     $users = User::whereIn('id', [3, 4, 5])->get();
//     foreach ($users as $user) {
//         $user->assignRole('HeadHR');
//     }
// }