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
         Schema::table('position_tables', function (Blueprint $table) {
            $table->text('role_summary')->nullable()->change();
            $table->dropColumn(['key_respon', 'qualifications', 'work_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('position_tables', function (Blueprint $table) {
            $table->string('role_summary', 255)->nullable()->change();
            $table->string('key_respon', 255)->nullable();
            $table->string('qualifications', 255)->nullable();
            $table->string('work_location', 255)->nullable();
        });
    }
};
