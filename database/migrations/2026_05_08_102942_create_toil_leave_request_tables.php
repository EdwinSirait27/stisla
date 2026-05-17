<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk klaim TOIL Leave dari karyawan.
     *
     * Flow:
     *  1. Karyawan request klaim → INSERT row dengan status='Pending'
     *  2. Atasan approve → UPDATE status='Approved'
     *                    → UPDATE toil_balances_tables.used_hours
     *                    → UPDATE rosters_tables.day_type='Off'
     *  3. Atasan reject → UPDATE status='Rejected' + rejected_reason
     */
    public function up(): void
    {
        Schema::create('toil_leave_requests_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Karyawan yang request
            $table->uuid('employee_id');

            // Saldo (toil_balances) yang dipakai
            $table->uuid('toil_balance_id');

            // Atasan yang approve/reject
            $table->uuid('approver_id');

            // Jam yang dipakai dari saldo (max 8 jam = 1 hari)
            $table->decimal('hours_used', 5, 2);

            // Tanggal libur yang diminta
            $table->date('leave_date');

            // Alasan klaim
            $table->text('reason')->nullable();

            // Status workflow
            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Cancelled',
            ])->default('Pending');

            // Audit approval
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')
                ->references('id')->on('employees_tables')
                ->onDelete('cascade');

            $table->foreign('toil_balance_id')
                ->references('id')->on('toil_balances_tables')
                ->onDelete('cascade');

            $table->foreign('approver_id')
                ->references('id')->on('employees_tables')
                ->onDelete('cascade');

            // Index untuk query yang sering dipakai
            $table->index(['employee_id', 'status'], 'idx_toil_lr_emp_status');
            $table->index(['approver_id', 'status'], 'idx_toil_lr_approver_status');
            $table->index(['leave_date'], 'idx_toil_lr_leave_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toil_leave_requests_tables');
    }
};