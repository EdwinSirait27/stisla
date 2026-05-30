<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores_tables', function (Blueprint $table) {
            $table->boolean('is_auto_generate')
                ->default(false)
                ->after('name')
                ->comment('Store ini bisa pakai Auto Generate Roster (static pattern Mon-Sat Work, Sun Off)');
        });

        // Set 3 store default jadi auto-generate
        DB::table('stores_tables')
            ->whereIn('name', ['Head Office', 'Holding', 'Distribution Center'])
            ->update(['is_auto_generate' => true]);
    }

    public function down(): void
    {
        Schema::table('stores_tables', function (Blueprint $table) {
            $table->dropColumn('is_auto_generate');
        });
    }
};