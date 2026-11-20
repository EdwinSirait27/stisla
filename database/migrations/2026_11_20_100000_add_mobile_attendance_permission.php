<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission mobile.attendance: boleh login & absen lewat aplikasi mobile.
 * Diberikan ke semua role yang ada saat migration dijalankan, supaya tidak ada karyawan
 * yang tiba-tiba terkunci. Cabut dari role tertentu lewat menu Role kalau perlu.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $perm = Permission::firstOrCreate(['name' => 'mobile.attendance', 'guard_name' => 'web']);
        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if (!$role->hasPermissionTo($perm)) $role->givePermissionTo($perm);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'mobile.attendance')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
