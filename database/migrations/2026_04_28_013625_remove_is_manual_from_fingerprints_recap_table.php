<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->dropColumn('is_manual');
            // ✅ Unique constraint 'uniq_employee_date' TIDAK dihapus
            //    karena masih dibutuhkan untuk mencegah duplicate
        });
    }

    public function down(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->boolean('is_manual')->default(0)->after('is_counted');
        });
    }
};