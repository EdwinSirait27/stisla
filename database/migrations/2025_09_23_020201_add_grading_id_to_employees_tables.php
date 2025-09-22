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
          Schema::table('employees_tables', function (Blueprint $table) {
            $table->uuid('grading_id')->nullable();
            $table->foreign('grading_id')
                ->references('id')
                ->on('grading')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees_tables', function (Blueprint $table) {
            //
        });
    }
};
