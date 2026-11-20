<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission untuk halaman Activity Log (sebelumnya ikut "dashboardAdmin").
 * Role yang memegang dashboardAdmin otomatis mendapat activity.view,
 * jadi tidak ada yang kehilangan akses dan tidak ada nama role yang di-hardcode.
 */
return new class extends Migration
{
    private const SOURCE = 'dashboardAdmin';
    private const NAME = 'activity.view';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => self::NAME, 'guard_name' => 'web']);

        $source = Permission::where('name', self::SOURCE)->where('guard_name', 'web')->first();
        if ($source) {
            foreach ($source->roles as $role) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', self::NAME)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
