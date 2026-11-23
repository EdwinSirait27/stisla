<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Halaman Registered Device: HR bisa melihat dan me-reset device mobile karyawan.
 * Device yang di-reset tidak dihapus (is_active = false) supaya riwayatnya tetap ada,
 * dan dicatat kapan serta oleh siapa reset dilakukan.
 */
return new class extends Migration
{
    private array $permissions = [
        'ManageRegisteredDevice',
        'ManageRegisteredDeviceSPVManager',
        'ViewRegisteredDevice',
    ];

    public function up(): void
    {
        Schema::table('registered_devices', function (Blueprint $table) {
            $table->timestamp('reset_at')->nullable()->after('registered_at');
            $table->uuid('reset_by')->nullable()->after('reset_at');
            $table->string('reset_reason', 255)->nullable()->after('reset_by');

            $table->foreign('reset_by')->references('id')->on('users')->nullOnDelete();
        });

        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('registered_devices', function (Blueprint $table) {
            $table->dropForeign(['reset_by']);
            $table->dropColumn(['reset_at', 'reset_by', 'reset_reason']);
        });

        Permission::whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
