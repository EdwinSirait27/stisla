<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tipe absen karyawan:
 * - store_bound       : wajib di radius store utama
 * - flexible          : boleh di radius store mana pun yang ditugaskan ke karyawan (multi-store)
 * - location_flexible : boleh dari mana saja, tapi absen di luar radius store ditandai is_offsite
 * - exempt            : tidak wajib absen (direksi/petinggi), absen lewat aplikasi ditolak
 *
 * Arti "flexible" lama = bebas lokasi, jadi data lama dipindah ke location_flexible supaya
 * karyawan yang sudah fleksibel tidak tiba-tiba ditolak saat absen di luar store.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE employees_tables MODIFY attendance_type ENUM('store_bound', 'flexible', 'location_flexible', 'exempt') NOT NULL DEFAULT 'store_bound'");
        DB::statement("UPDATE employees_tables SET attendance_type = 'location_flexible' WHERE attendance_type = 'flexible'");

        Schema::table('attendance_logs', function (Blueprint $table) {
            // Snapshot tipe absen saat itu, supaya riwayat tetap benar kalau tipe karyawan diubah
            $table->string('attendance_mode', 30)->nullable()->after('type');
            // Penanda khusus untuk HR: absen location_flexible di luar radius store mana pun
            $table->boolean('is_offsite')->default(false)->after('is_within_geofence');
            $table->index('is_offsite');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['is_offsite']);
            $table->dropColumn(['attendance_mode', 'is_offsite']);
        });

        DB::statement("UPDATE employees_tables SET attendance_type = 'flexible' WHERE attendance_type = 'location_flexible'");
        DB::statement("UPDATE employees_tables SET attendance_type = 'store_bound' WHERE attendance_type = 'exempt'");
        DB::statement("ALTER TABLE employees_tables MODIFY attendance_type ENUM('store_bound', 'flexible') NOT NULL DEFAULT 'store_bound'");
    }
};
