<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Persetujuan bertingkat absensi mobile:
 *   pending     → diajukan (entri manual), menunggu checker
 *   approved    → lolos checker (SPV/manager), atau absen aplikasi yang lolos verifikasi sistem; menunggu HR
 *   approved_hr → disetujui HR, final
 *   flagged     → ditandai bermasalah oleh HR
 * Ditambah jejak siapa mengajukan, mengecek, dan menyetujui.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('pending', 'approved', 'approved_hr', 'flagged') NOT NULL DEFAULT 'pending'");

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('flag_reason');      // pengaju entri manual (null = absen aplikasi)
            $table->uuid('checked_by')->nullable()->after('created_by');       // checker (SPV/manager)
            $table->timestamp('checked_at')->nullable()->after('checked_by');
            $table->uuid('hr_approved_by')->nullable()->after('checked_at');   // HR
            $table->timestamp('hr_approved_at')->nullable()->after('hr_approved_by');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('checked_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('hr_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['checked_by']);
            $table->dropForeign(['hr_approved_by']);
            $table->dropColumn(['created_by', 'checked_by', 'checked_at', 'hr_approved_by', 'hr_approved_at']);
        });

        DB::statement("UPDATE attendance_logs SET status = 'approved' WHERE status = 'approved_hr'");
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('approved', 'pending', 'flagged') DEFAULT 'pending'");
    }
};
