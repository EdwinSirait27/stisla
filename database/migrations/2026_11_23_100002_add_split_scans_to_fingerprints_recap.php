<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scan tengah untuk split shift.
 *   time_in   = masuk blok 1
 *   break_out = pulang blok 1
 *   break_in  = masuk blok 2
 *   time_out  = pulang blok 2
 * Hari dengan shift biasa: break_out/break_in kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->time('break_out')->nullable()->after('time_in');
            $table->time('break_in')->nullable()->after('break_out');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprints_recap', function (Blueprint $table) {
            $table->dropColumn(['break_out', 'break_in']);
        });
    }
};
