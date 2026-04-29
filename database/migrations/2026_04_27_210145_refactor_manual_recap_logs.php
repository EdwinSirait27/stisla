<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: refactor manual_recap_logs 
 *
 * ACTIONS:
 * 1. Drop tabel manual_recap_evidences (kolom-kolomnya pindah ke logs)
 * 2. Modifikasi manual_recap_logs:
 *    - PERTAHANKAN: id, employee_id, reason
 *    - DROP:        pin, date, time_in, time_out, hr_id, hr_name,
 *                   submitted_at, email_sent, email_sent_at,
 *                   whatsapp_sent, whatsapp_sent_at, notification_error
 *    - TAMBAH:      file_name, file_path, mime_type, file_size
 *
 * Behavior baru: 1 row per FILE
 * Kalau HR upload 3 file × 2 karyawan × 5 hari = 30 rows
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Step 1: Drop tabel manual_recap_evidences (kolomnya pindah) ──
        Schema::dropIfExists('manual_recap_evidences');

        // ── Step 2: Modifikasi tabel manual_recap_logs ──
        Schema::table('manual_recap_logs', function (Blueprint $table) {
            // Drop kolom yang tidak dipakai lagi
            $columnsToDrop = [
                'pin',
                'date',
                'time_in',
                'time_out',
                'hr_id',
                'hr_name',
                'submitted_at',
                'email_sent',
                'email_sent_at',
                'whatsapp_sent',
                'whatsapp_sent_at',
                'notification_error',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('manual_recap_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // ── Step 3: Tambah kolom file (separate untuk avoid conflict di MySQL) ──
        Schema::table('manual_recap_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('manual_recap_logs', 'file_name')) {
                $table->string('file_name')->after('reason');
            }
            if (!Schema::hasColumn('manual_recap_logs', 'file_path')) {
                $table->string('file_path')->after('file_name');
            }
            if (!Schema::hasColumn('manual_recap_logs', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('manual_recap_logs', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            }
        });
    }

    public function down(): void
    {
        // ── Rollback: kembalikan struktur lama ──
        Schema::table('manual_recap_logs', function (Blueprint $table) {
            // Drop kolom file
            $fileCols = ['file_name', 'file_path', 'mime_type', 'file_size'];
            foreach ($fileCols as $col) {
                if (Schema::hasColumn('manual_recap_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('manual_recap_logs', function (Blueprint $table) {
            // Tambah kolom lama balik
            $table->string('pin')->nullable();
            $table->date('date')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->uuid('hr_id')->nullable();
            $table->string('hr_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('whatsapp_sent')->default(false);
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->text('notification_error')->nullable();
        });

        // Recreate manual_recap_evidences
        Schema::create('manual_recap_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('manual_recap_log_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }
};