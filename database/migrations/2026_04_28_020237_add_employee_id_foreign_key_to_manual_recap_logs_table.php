<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_recap_logs', function (Blueprint $table) {
            // Tambah index dulu (kalau belum ada) supaya FK bisa dibuat
            // Dan untuk performa query yang filter by employee_id
            $table->foreign('employee_id', 'fk_manual_recap_logs_employee_id')
                ->references('id')
                ->on('employees_tables')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // ON DELETE CASCADE: kalau karyawan dihapus, log Manual Recap-nya
            //                   ikut terhapus (karena log tidak berguna tanpa karyawan)
            //
            // Alternatif kalau mau preserve audit trail:
            //   ->onDelete('restrict')  → tidak boleh hapus karyawan kalau punya log
            //   ->onDelete('set null')  → set employee_id jadi NULL (perlu kolom nullable)
        });
    }

    public function down(): void
    {
        Schema::table('manual_recap_logs', function (Blueprint $table) {
            $table->dropForeign('fk_manual_recap_logs_employee_id');
        });
    }
};