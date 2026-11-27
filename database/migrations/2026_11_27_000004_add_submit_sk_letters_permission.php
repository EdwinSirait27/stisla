<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * SK tidak lagi dikunci role HeadHR: mengubah Draft, mengajukan, dan membatalkan SK memakai
 * permission SubmitSkLetters (bersama ManageSkLetters). Awalnya diberikan ke HeadHR (yang
 * sebelumnya bisa) dan Admin; role lain diberikan lewat menu Role.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('SubmitSkLetters', 'web');
        foreach (['HeadHR', 'Admin'] as $name) {
            Role::where('name', $name)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'SubmitSkLetters')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
