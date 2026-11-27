<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SK setelah modul struktur (Structuresnew) dihapus:
 * - Per karyawan SK mencatat penempatan baru langsung: jabatan, departemen, lokasi, grading, group,
 *   dan jenis karyawan baru (untuk SK pengangkatan / kontrak). Kolom *_structure_id lama tetap ada
 *   untuk SK lama.
 * - applied_at / salary_id: kapan perubahan diterapkan ke data karyawan & baris gaji yang dibuat.
 * - Approval per SK (approver dipilih di SK): salinan tanda tangan tiap approver, catatan
 *   pengembalian, dan waktu disetujui penuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sk_letter_employees', function (Blueprint $table) {
            $table->char('previous_store_id', 36)->nullable()->after('department_id');
            $table->char('previous_status_employee', 50)->nullable()->after('previous_store_id');
            $table->char('new_position_id', 36)->nullable()->after('new_structure_id');
            $table->char('new_department_id', 36)->nullable()->after('new_position_id');
            $table->char('new_store_id', 36)->nullable()->after('new_department_id');
            $table->char('new_grading_id', 36)->nullable()->after('new_store_id');
            $table->char('new_group_id', 36)->nullable()->after('new_grading_id');
            $table->string('new_status_employee', 50)->nullable()->after('new_group_id');
            $table->timestamp('applied_at')->nullable()->after('notes');
            $table->char('salary_id', 36)->nullable()->after('applied_at');
        });

        Schema::table('sk_letters', function (Blueprint $table) {
            $table->string('approver_1_signature')->nullable()->after('approver_3_at');
            $table->string('approver_2_signature')->nullable()->after('approver_1_signature');
            $table->string('approver_3_signature')->nullable()->after('approver_2_signature');
            $table->text('returned_note')->nullable()->after('notes');
            $table->char('returned_by', 36)->nullable()->after('returned_note');
            $table->timestamp('returned_at')->nullable()->after('returned_by');
            $table->timestamp('completed_at')->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('sk_letters', function (Blueprint $table) {
            $table->dropColumn(['approver_1_signature', 'approver_2_signature', 'approver_3_signature', 'returned_note', 'returned_by', 'returned_at', 'completed_at']);
        });
        Schema::table('sk_letter_employees', function (Blueprint $table) {
            $table->dropColumn(['previous_store_id', 'previous_status_employee', 'new_position_id', 'new_department_id', 'new_store_id', 'new_grading_id', 'new_group_id', 'new_status_employee', 'applied_at', 'salary_id']);
        });
    }
};
