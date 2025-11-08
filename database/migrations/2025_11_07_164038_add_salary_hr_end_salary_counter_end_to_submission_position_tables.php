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
        Schema::table('submission_position_tables', function (Blueprint $table) {
            $table->text('salary_hr_end')->nullable();
            $table->text('salary_counter_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_position_tables', function (Blueprint $table) {
            //
        });
    }
};
