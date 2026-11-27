<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kontrak setelah modul struktur dihapus:
 * - store_id: lokasi kerja di kontrak (sebelumnya hanya lewat struktur).
 * - contract_type + PKWTT (karyawan tetap, tanpa tanggal berakhir).
 * - Pengakhiran: kapan, alasan, oleh siapa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->char('store_id', 36)->nullable()->after('department_id');
            $table->date('terminated_at')->nullable()->after('contract_status');
            $table->text('terminated_reason')->nullable()->after('terminated_at');
            $table->char('terminated_by', 36)->nullable()->after('terminated_reason');
            $table->char('created_by', 36)->nullable()->after('notes');
        });
        DB::statement("ALTER TABLE contracts MODIFY contract_type ENUM('PKWT','PKWTT','On Job Training','DW') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE contracts SET contract_type = 'PKWT' WHERE contract_type = 'PKWTT'");
        DB::statement("ALTER TABLE contracts MODIFY contract_type ENUM('PKWT','On Job Training','DW') NOT NULL");
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'terminated_at', 'terminated_reason', 'terminated_by', 'created_by']);
        });
    }
};
