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
        Schema::table('toil_leave_requests_tables', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('id');
            $table->string('original_day_type')->nullable()->after('original_shift_id');
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toil_leave_requests_tables', function (Blueprint $table) {
             $table->dropIndex(['batch_id']);
             $table->dropColumn(['batch_id', 'original_day_type']);
        });
    }
};
