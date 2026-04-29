<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->boolean('is_manual')->default(0)->after('is_counted');

            // Optional tapi sangat disarankan (anti duplicate)
            $table->unique(['employee_id', 'date'], 'uniq_employee_date');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->dropUnique('uniq_employee_date');
            $table->dropColumn('is_manual');
        });
    }
};