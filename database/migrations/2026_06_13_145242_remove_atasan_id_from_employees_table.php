<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    Schema::table('employees_tables', function (Blueprint $table) {
        $table->dropForeign(['atasan_id']);
        $table->dropColumn('atasan_id');
    });
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}

public function down(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    Schema::table('employees_tables', function (Blueprint $table) {
        $table->uuid('atasan_id')->nullable();
    });
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}
};
