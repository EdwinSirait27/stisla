<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * PayrollService menandai saldo Cash sebagai 'paid', tetapi nilai itu belum
     * pernah ada di migration. Di server yang di-migrate dari nol, generate
     * payroll gagal untuk karyawan yang punya lembur Cash.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE toil_balances_tables MODIFY COLUMN status
            ENUM('active','fully_used','expired','cancelled','paid')
            NOT NULL DEFAULT 'active'");
    }

    /**
     * Sengaja tidak menghapus 'paid': baris yang sudah dibayar akan rusak.
     */
    public function down(): void
    {
        //
    }
};
