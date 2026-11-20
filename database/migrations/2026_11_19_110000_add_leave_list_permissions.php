<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Daftar cuti (sebelumnya "ManageSummaries" / SummaryController) dipecah per cakupan:
 *  - leave.list.view          : semua karyawan
 *  - leave.list.view.manager  : diri sendiri + bawahan langsung (employee_atasans)
 *  - leave.list.view.human    : diri sendiri
 * Role pemegang ManageSummaries otomatis mendapat leave.list.view (akses tidak berubah).
 * Dua permission lainnya membuka akses baru, jadi tidak diberikan otomatis: atur di menu Role.
 */
return new class extends Migration
{
    private const OLD = 'ManageSummaries';
    private const ALL = 'leave.list.view';
    private const NEW = ['leave.list.view', 'leave.list.view.manager', 'leave.list.view.human'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::NEW as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $old = Permission::where('name', self::OLD)->where('guard_name', 'web')->first();
        if ($old) {
            foreach ($old->roles as $role) {
                $role->givePermissionTo(self::ALL);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', self::NEW)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
