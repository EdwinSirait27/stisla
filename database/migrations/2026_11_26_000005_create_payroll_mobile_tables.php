<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Payroll versi absensi mobile, terpisah total dari payroll fingerprint:
 * - Tabel sendiri dengan struktur sama (CREATE TABLE ... LIKE): payroll_mobile_periods,
 *   payroll_mobiles, payroll_mobile_details. Tabel payroll fingerprint tidak diubah.
 * - Kolom tambahan payroll_mobiles: attendance_remarks (rincian hadir dari absensi mobile) dan
 *   overtime_submission_ids (lembur yang dikunci payroll ini).
 * - Permission sendiri: payroll.mobile.* (awalnya hanya Admin, sama seperti payroll fingerprint).
 */
return new class extends Migration
{
    public const PERMISSIONS = [
        'payroll.mobile.view', 'payroll.mobile.show',
        'payroll.mobile.generate', 'payroll.mobile.generate.one',
        'payroll.mobile.update', 'payroll.mobile.destroy', 'payroll.mobile.destroy.bulk',
        'payroll.mobile.approve', 'payroll.mobile.approve.bulk', 'payroll.mobile.paid',
        'payroll.mobile.export', 'payroll.mobile.import', 'payroll.mobile.download.template',
        'payroll.mobile.download.slip', 'payroll.mobile.slip.bulk', 'payroll.mobile.send.slip', 'payroll.mobile.send.slip.bulk',
        'payroll.mobile.period.view', 'payroll.mobile.period.create', 'payroll.mobile.period.close', 'payroll.mobile.period.lock',
    ];

    public function up(): void
    {
        DB::statement('CREATE TABLE payroll_mobile_periods LIKE payroll_periods');
        DB::statement('CREATE TABLE payroll_mobiles LIKE payrolls');
        DB::statement('CREATE TABLE payroll_mobile_details LIKE payroll_details');

        // LIKE tidak menyalin foreign key: dibuat ulang dengan nama sendiri
        Schema::table('payroll_mobile_periods', function (Blueprint $table) {
            $table->foreign('created_by', 'pm_periods_created_by_fk')->references('id')->on('employees_tables')->nullOnDelete();
            $table->foreign('locked_by', 'pm_periods_locked_by_fk')->references('id')->on('employees_tables')->nullOnDelete();
        });
        Schema::table('payroll_mobiles', function (Blueprint $table) {
            $table->text('attendance_remarks')->nullable()->after('note');
            $table->json('overtime_submission_ids')->nullable()->after('attendance_remarks');
            $table->foreign('approved_by', 'pm_approved_by_fk')->references('id')->on('employees_tables')->nullOnDelete();
            $table->foreign('employee_id', 'pm_employee_id_fk')->references('id')->on('employees_tables')->cascadeOnDelete();
            $table->foreign('payroll_period_id', 'pm_period_id_fk')->references('id')->on('payroll_mobile_periods')->nullOnDelete();
        });
        Schema::table('payroll_mobile_details', function (Blueprint $table) {
            $table->foreign('payroll_component_id', 'pmd_component_id_fk')->references('id')->on('payroll_components')->cascadeOnDelete();
            $table->foreign('payroll_id', 'pmd_payroll_id_fk')->references('id')->on('payroll_mobiles')->cascadeOnDelete();
        });

        $admin = Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        foreach (self::PERMISSIONS as $name) {
            $permission = Permission::findOrCreate($name, 'web');
            $admin?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_mobile_details');
        Schema::dropIfExists('payroll_mobiles');
        Schema::dropIfExists('payroll_mobile_periods');
        Permission::whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
