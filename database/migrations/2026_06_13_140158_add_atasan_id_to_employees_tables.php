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
         Schema::disableForeignKeyConstraints(); // ← tambah ini

    Schema::table('employees_tables', function (Blueprint $table) {
        $table->uuid('atasan_id')->nullable()->after('grading_id');
        $table->foreign('atasan_id')
              ->references('id')
              ->on('employees_tables')
              ->nullOnDelete();
    });

    Schema::enableForeignKeyConstraints(); // ← dan ini
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::disableForeignKeyConstraints();

    Schema::table('employees_tables', function (Blueprint $table) {
        $table->dropForeign(['atasan_id']);
        $table->dropColumn('atasan_id');
    });

    Schema::enableForeignKeyConstraints();
}
};
