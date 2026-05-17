<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toil_leave_requests_tables', function (Blueprint $table) {
            $table->char('original_shift_id', 36)
                ->nullable()
                ->after('hours_used')
                ->comment('Simpan shift_id sebelum roster diubah jadi Off, untuk restore saat cancel');
        });
    }

    public function down(): void
    {
        Schema::table('toil_leave_requests_tables', function (Blueprint $table) {
            $table->dropColumn('original_shift_id');
        });
    }
};