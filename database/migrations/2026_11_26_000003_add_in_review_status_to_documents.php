<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Berita acara: status "in_review" (disetujui sebagian) setelah approver pertama menyetujui dan
 * sebelum approver terakhir. Sebelumnya status tetap "pending" sampai semua menyetujui.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft','issued','revoked','expired','pending','in_review','revision','approved','cancelled') NOT NULL DEFAULT 'draft'");

        // Berita acara yang sedang berjalan dan sudah disetujui sebagian
        DB::statement("UPDATE documents d SET d.status = 'in_review' WHERE d.status = 'pending' AND EXISTS (
            SELECT 1 FROM document_approvals a JOIN document_contents c ON c.document_id = a.document_id
            WHERE a.document_id = d.id AND a.round = c.round AND a.status = 'approved')");
    }

    public function down(): void
    {
        DB::statement("UPDATE documents SET status = 'pending' WHERE status = 'in_review'");
        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft','issued','revoked','expired','pending','revision','approved','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
