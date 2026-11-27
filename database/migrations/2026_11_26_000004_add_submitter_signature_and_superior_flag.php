<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PDF berita acara:
 * - document_contents.submitter_signature_path: salinan tanda tangan pengaju saat diajukan
 *   (seperti approver), supaya PDF tidak berubah kalau pengaju mengganti tanda tangan profil.
 * - document_approvals.is_superior: langkah ini atasan pengaju, tampil "Mengetahui" di PDF dan
 *   tidak dihitung sebagai "Disetujui (n)".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_contents', function (Blueprint $table) {
            $table->string('submitter_signature_path')->nullable()->after('submitted_at');
        });
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->boolean('is_superior')->default(false)->after('sequence');
        });

        // Data lama: approver pertama yang merupakan atasan pengaju
        DB::statement("UPDATE document_approvals a
            JOIN documents d ON d.id = a.document_id
            JOIN employee_atasans ea ON ea.employee_id = d.employee_id AND ea.atasan_id = a.employee_id
            SET a.is_superior = 1
            WHERE a.sequence = 1");
    }

    public function down(): void
    {
        Schema::table('document_approvals', fn(Blueprint $table) => $table->dropColumn('is_superior'));
        Schema::table('document_contents', fn(Blueprint $table) => $table->dropColumn('submitter_signature_path'));
    }
};
