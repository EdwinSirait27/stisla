<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Berita acara dengan approval bertingkat.
 *
 * - document_types.requires_approval: jenis dokumen yang memakai alur ini (berita acara, dst).
 * - documents.status ditambah: pending (menunggu persetujuan), revision (perlu revisi),
 *   approved (disetujui semua), cancelled (dibatalkan pengaju). Nilai lama tetap.
 * - document_contents: isi berita acara (perihal, waktu, tempat, uraian).
 * - document_approvals: approver berurutan, status per langkah, tanda tangan saat disetujui.
 * - document_recipients: tembusan.
 * - document_attachments: lampiran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('is_active');
        });

        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft','issued','revoked','expired','pending','revision','approved','cancelled') NOT NULL DEFAULT 'draft'");

        Schema::create('document_contents', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('document_id')->unique();
            $table->string('subject');                    // perihal
            $table->dateTime('event_at')->nullable();     // waktu kejadian
            $table->string('location')->nullable();       // tempat
            $table->longText('body');                     // uraian
            $table->unsignedSmallInteger('round')->default(1); // putaran pengajuan (naik setiap diajukan ulang)
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
        });

        Schema::create('document_approvals', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->char('employee_id', 36);
            $table->unsignedTinyInteger('sequence');      // urutan sesuai pilihan pengaju, mulai 1
            $table->unsignedSmallInteger('round')->default(1);
            // waiting = menunggu giliran, pending = giliran menyetujui, approved = disetujui,
            // returned = dikembalikan untuk revisi, voided = batal karena diajukan ulang / dibatalkan
            $table->enum('status', ['waiting', 'pending', 'approved', 'returned', 'voided'])->default('waiting');
            $table->string('signature_path')->nullable(); // salinan tanda tangan saat menyetujui
            $table->text('note')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
            $table->index(['employee_id', 'status']);
            $table->index(['document_id', 'round', 'sequence']);
        });

        Schema::create('document_recipients', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->char('employee_id', 36);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
            $table->unique(['document_id', 'employee_id']);
        });

        Schema::create('document_attachments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
        });

        // Permission membuat berita acara. Melihat & menyetujui tidak butuh permission:
        // aksesnya dari keterlibatan (pengaju, approver, tembusan) atau ManageBeritaAcara.
        // Awalnya hanya Admin; role lain diberikan lewat pengaturan role.
        $permission = Permission::findOrCreate('CreateBeritaAcara', 'web');
        Role::where('name', 'Admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('document_recipients');
        Schema::dropIfExists('document_approvals');
        Schema::dropIfExists('document_contents');
        DB::statement("UPDATE documents SET status = 'draft' WHERE status IN ('pending','revision','approved','cancelled')");
        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft','issued','revoked','expired') NOT NULL DEFAULT 'draft'");
        Schema::table('document_types', fn(Blueprint $table) => $table->dropColumn('requires_approval'));
        Permission::where('name', 'CreateBeritaAcara')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
