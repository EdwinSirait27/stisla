<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Path lampiran bukti di S3 (mis. surat dokter untuk Cuti Melahirkan).
            // Nullable: cuti biasa tidak wajib melampirkan apa pun.
            $table->string('attachment', 255)->nullable()->after('employee_reason');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};