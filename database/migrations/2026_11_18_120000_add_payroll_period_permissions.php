<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Pecah permission lama "ManagePayrollPeriod" menjadi view / create / close / lock.
 * Role yang memegang permission lama otomatis mendapat semua permission baru,
 * jadi tidak ada yang kehilangan akses dan tidak ada nama role yang di-hardcode.
 */
return new class extends Migration
{
    private const OLD = 'ManagePayrollPeriod';

    private const NEW = [
        'payroll.period.view',
        'payroll.period.create',
        'payroll.period.close',
        'payroll.period.lock',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $created = collect(self::NEW)->map(
            fn($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
        );

        $old = Permission::where('name', self::OLD)->where('guard_name', 'web')->first();
        if ($old) {
            foreach ($old->roles as $role) {
                $role->givePermissionTo($created);
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
