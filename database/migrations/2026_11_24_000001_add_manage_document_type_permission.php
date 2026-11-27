<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission halaman Jenis dokumen (master document_types). Diberikan otomatis ke role yang
 * sudah bisa mengelola dokumen (ManageDocument), supaya halaman langsung bisa dipakai tanpa ubah DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('ManageDocumentType', 'web');
        foreach (Role::permission('ManageDocument')->get() as $role) {
            $role->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'ManageDocumentType')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
