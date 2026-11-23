<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesi absen per tanggal kerja. Shift biasa selalu sesi 1; split shift punya sesi 1 (blok 1) dan 2 (blok 2),
 * masing-masing dengan satu check-in dan satu check-out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('session')->default(1)->after('type');
        });

        // Unique baru dibuat dulu supaya FK employee_id tetap punya index saat yang lama dihapus
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unique(['employee_id', 'work_date', 'type', 'session'], 'unique_attendance_session_entry');
        });
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('unique_attendance_entry');
        });
    }

    public function down(): void
    {
        // Gagal kalau sudah ada data sesi 2; hapus dulu sebelum rollback.
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unique(['employee_id', 'work_date', 'type'], 'unique_attendance_entry');
        });
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('unique_attendance_session_entry');
            $table->dropColumn('session');
        });
    }
};
