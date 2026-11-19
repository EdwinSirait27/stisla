<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * - original_notes: catatan roster asli, dikembalikan saat cancel.
     * - approved_leave_date + unique: satu karyawan hanya boleh punya satu TOIL
     *   Approved per tanggal. Kolom bernilai NULL untuk status lain, jadi baris
     *   Cancelled / Rejected tidak ikut dibatasi.
     */
    public function up(): void
    {
        $duplicates = DB::select("
            SELECT employee_id, leave_date, COUNT(*) AS total
            FROM toil_leave_requests_tables
            WHERE status = 'Approved'
            GROUP BY employee_id, leave_date
            HAVING COUNT(*) > 1
        ");

        if (!empty($duplicates)) {
            throw new RuntimeException(
                'Ada TOIL Approved ganda, bersihkan dulu sebelum migrate: ' . json_encode($duplicates)
            );
        }

        DB::statement('ALTER TABLE toil_leave_requests_tables ADD COLUMN original_notes TEXT NULL AFTER original_day_type');

        DB::statement("
            ALTER TABLE toil_leave_requests_tables
            ADD COLUMN approved_leave_date DATE
            AS (IF(status = 'Approved', leave_date, NULL)) STORED
        ");

        DB::statement('
            ALTER TABLE toil_leave_requests_tables
            ADD UNIQUE INDEX toil_lr_emp_approved_date_unique (employee_id, approved_leave_date)
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE toil_leave_requests_tables DROP INDEX toil_lr_emp_approved_date_unique');
        DB::statement('ALTER TABLE toil_leave_requests_tables DROP COLUMN approved_leave_date');
        DB::statement('ALTER TABLE toil_leave_requests_tables DROP COLUMN original_notes');
    }
};
