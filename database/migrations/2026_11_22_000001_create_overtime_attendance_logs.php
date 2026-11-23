<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Absensi lembur dari aplikasi mobile (selfie + GPS), sama seperti attendance_logs, tapi
 * setiap log terikat ke satu lembur yang sudah disetujui dan punya saldo di toil_balances_tables.
 * Satu lembur hanya punya satu check-in dan satu check-out.
 */
return new class extends Migration
{
    private array $permissions = [
        'ManageOvertimeAttendanceMobile',
        'ManageOvertimeAttendanceMobileSPVManager',
        'ViewOvertimeAttendanceMobile',
    ];

    public function up(): void
    {
        Schema::create('overtime_attendance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('overtime_submission_id');
            $table->uuid('toil_balance_id')->nullable();
            $table->uuid('store_id')->nullable();
            $table->date('work_date'); // = tanggal lembur (overtime_submissions_tables.date)
            $table->enum('type', ['checkin', 'checkout']);
            $table->string('attendance_mode', 30)->nullable(); // snapshot attendance_type, atau 'manual'

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_from_store', 8, 2)->nullable();
            $table->boolean('is_within_geofence')->default(false);
            $table->boolean('is_offsite')->default(false);
            $table->boolean('is_mock_location')->default(false);

            $table->string('photo_path')->nullable();
            $table->decimal('liveness_score', 5, 2)->nullable();
            $table->boolean('liveness_passed')->default(false);

            $table->string('device_id')->nullable();
            $table->enum('status', ['approved', 'pending', 'flagged'])->default('pending');
            $table->text('flag_reason')->nullable();

            $table->timestamp('logged_at');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('overtime_submission_id')->references('id')->on('overtime_submissions_tables')->onDelete('cascade');
            $table->foreign('toil_balance_id')->references('id')->on('toil_balances_tables')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('stores_tables')->nullOnDelete();

            $table->unique(['overtime_submission_id', 'type'], 'unique_overtime_attendance_entry');
            $table->index(['employee_id', 'work_date']);
            $table->index('status');
            $table->index('is_offsite');
        });

        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_attendance_logs');

        Permission::whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
