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
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->date('period_in')->nullable()->after('date');
            $table->date('period_out')->nullable()->after('period_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->dropColumn(['period_in', 'period_out']);
        });
    }
};
