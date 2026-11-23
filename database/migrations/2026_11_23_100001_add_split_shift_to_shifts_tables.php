<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Split shift: satu shift dengan dua blok kerja di hari yang sama.
 *   Blok 1: start_time  → break_start
 *   Blok 2: break_end   → end_time
 * Shift biasa: is_split = false, break_start/break_end kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts_tables', function (Blueprint $table) {
            $table->boolean('is_split')->default(false)->after('end_time');
            $table->time('break_start')->nullable()->after('is_split'); // jam pulang blok 1
            $table->time('break_end')->nullable()->after('break_start'); // jam masuk blok 2
        });
    }

    public function down(): void
    {
        Schema::table('shifts_tables', function (Blueprint $table) {
            $table->dropColumn(['is_split', 'break_start', 'break_end']);
        });
    }
};
