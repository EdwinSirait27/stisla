<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perluas dulu enum supaya value lama & baru bisa hidup berdampingan sementara
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('valid', 'flagged', 'rejected', 'approved', 'pending') DEFAULT 'pending'");

        // 2. Baru migrasikan data lama ke value baru
        DB::statement("UPDATE attendance_logs SET status = 'approved' WHERE status = 'valid'");
        DB::statement("UPDATE attendance_logs SET status = 'flagged' WHERE status = 'rejected'");

        // 3. Setelah semua data sudah pakai value baru, persempit enum ke value final saja
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('approved', 'pending', 'flagged') DEFAULT 'pending'");

        // 4. Buat latitude, longitude, device_id nullable
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
            $table->string('device_id')->nullable()->change();
        });

        // 5. Tambah unique constraint
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unique(['employee_id', 'work_date', 'type'], 'unique_attendance_entry');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('unique_attendance_entry');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
            $table->string('device_id')->nullable(false)->change();
        });

        // Perluas dulu sebelum revert data
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('valid', 'flagged', 'rejected', 'approved', 'pending') DEFAULT 'valid'");
        DB::statement("UPDATE attendance_logs SET status = 'valid' WHERE status = 'approved'");
        DB::statement("UPDATE attendance_logs SET status = 'rejected' WHERE status = 'flagged'");
        DB::statement("ALTER TABLE attendance_logs MODIFY status ENUM('valid', 'flagged', 'rejected') DEFAULT 'valid'");
    }
};