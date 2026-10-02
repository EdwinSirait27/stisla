<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permission untuk mengizinkan manager assign lembur ke diri sendiri
     * (TOIL assignment). Diberikan ke role/user lewat halaman Roles & Permissions.
     */
    public function up(): void
    {
        Permission::findOrCreate('assignmentSelf', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'assignmentSelf')->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
