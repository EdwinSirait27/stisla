<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests_tables', function (Blueprint $table) {
            $table->dropForeign('leave_requests_tables_approver_id_foreign');

        // Baru drop kolomnya
        $table->dropColumn(['approver_id', 'approved_at']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests_tables', function (Blueprint $table) {
            //
        });
    }
};
