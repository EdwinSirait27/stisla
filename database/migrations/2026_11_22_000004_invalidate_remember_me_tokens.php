<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fitur "ingat saya" dihapus. Cookie remember yang mungkin masih tersimpan di browser
 * dibatalkan dengan mengosongkan remember_token semua user, supaya tidak ada lagi login
 * otomatis yang melewati batas waktu session. Sesi yang sedang aktif tidak terpengaruh.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
    }

    public function down(): void
    {
        // Token lama tidak bisa dikembalikan, dan memang tidak perlu
    }
};
