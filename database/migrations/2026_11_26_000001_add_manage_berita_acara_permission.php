<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission berita acara dipisah dari ManageDocument (dokumen terbitan HR):
 * - CreateBeritaAcara : membuat & mengajukan berita acara (sudah ada).
 * - ManageBeritaAcara : melihat semua berita acara semua karyawan (tab Semua, detail, lampiran, PDF).
 * Menyetujui tetap tanpa permission: hanya approver yang mendapat giliran.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('ManageBeritaAcara', 'web');
        // Awalnya hanya Admin, supaya akses yang ada sekarang tidak hilang
        Role::where('name', 'Admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'ManageBeritaAcara')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
