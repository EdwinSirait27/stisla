<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Surat tugas bertanggal mulai di masa depan: tujuan disimpan dulu sebagai rencana (applied_at kosong,
 * new_pivot_id kosong) dan baru diterapkan oleh documents:expire-surat-tugas saat tanggal mulai tiba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_temporary_assignments', function (Blueprint $table) {
            $table->char('new_pivot_id', 36)->nullable()->change();
            $table->timestamp('applied_at')->nullable()->after('is_primary_change');
        });

        // Baris lama selalu langsung diterapkan saat dibuat
        DB::table('document_temporary_assignments')->whereNull('applied_at')->update(['applied_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        DB::table('document_temporary_assignments')->whereNull('new_pivot_id')->delete();
        Schema::table('document_temporary_assignments', function (Blueprint $table) {
            $table->dropColumn('applied_at');
            $table->char('new_pivot_id', 36)->nullable(false)->change();
        });
    }
};
