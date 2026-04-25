<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk audit trail manual recap absensi.
     *
     * Dipakai ketika HR klik "+Add Recap" untuk override
     * absensi karyawan yang tidak scan.
     *
     * Data di sini TIDAK menggantikan fingerprints_recap,
     * tapi sebagai CATATAN TAMBAHAN & PERTANGGUNGJAWABAN.
     *
     * Relasi:
     *   - employee_id  → employees_tables (karyawan yang di-override)
     *   - hr_id        → employees_tables (HR pengaju)
     *   - evidences    → manual_recap_evidences (1:many file bukti)
     */
    public function up(): void
    {
        Schema::create('manual_recap_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Data karyawan & tanggal yang di-override
            $table->uuid('employee_id');
            $table->string('pin')->nullable();
            $table->date('date');

            // Detail override
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->text('reason'); // min 10 char, wajib

            // ── HR yang mengajukan (bukan user login) ──
            $table->uuid('hr_id')->nullable();           // ref ke employees_tables.id
            $table->string('hr_name')->nullable();       // snapshot nama HR
            $table->timestamp('submitted_at')->nullable();

            // Status notifikasi
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('whatsapp_sent')->default(false);
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->text('notification_error')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index('submitted_at');
            $table->index('hr_id');
        });

        // ── Tabel bukti (1:many relasi ke manual_recap_logs) ──
        Schema::create('manual_recap_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('manual_recap_log_id');
            $table->string('file_name');       // nama asli dari user
            $table->string('file_path');       // path di storage
            $table->string('mime_type');       // contoh: image/jpeg, application/pdf
            $table->unsignedBigInteger('file_size'); // dalam bytes

            $table->timestamps();

            $table->foreign('manual_recap_log_id')
                ->references('id')
                ->on('manual_recap_logs')
                ->onDelete('cascade');

            $table->index('manual_recap_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_recap_evidences');
        Schema::dropIfExists('manual_recap_logs');
    }
};