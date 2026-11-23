<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission master bank dipecah per aksi:
 *   bank.view   → daftar bank (index, getBanks)
 *   bank.create → tambah bank (create, store)
 *   bank.update → ubah bank (edit, update)
 *
 * Role dan user yang sebelumnya punya ManageBanks mendapat ketiganya, supaya tidak ada yang kehilangan akses.
 * ManageBanks sendiri tidak dihapus; route sudah tidak memakainya.
 */
return new class extends Migration
{
    private array $permissions = ['bank.view', 'bank.create', 'bank.update'];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $legacy = Permission::where('name', 'ManageBanks')->where('guard_name', 'web')->first();
        if ($legacy) {
            foreach (Role::permission('ManageBanks')->get() as $role) {
                $role->givePermissionTo($this->permissions);
            }

            // Permission yang diberikan langsung ke user (bukan lewat role)
            $direct = DB::table(config('permission.table_names.model_has_permissions'))
                ->where('permission_id', $legacy->id)
                ->get(['model_type', 'model_id']);
            foreach ($direct as $row) {
                $model = $row->model_type::find($row->model_id);
                $model?->givePermissionTo($this->permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
