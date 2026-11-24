<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission halaman Pengaturan TOIL. Diberikan ke role/user lewat halaman Roles & Permissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Permission::findOrCreate('ManageToilSetting', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'ManageToilSetting')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
