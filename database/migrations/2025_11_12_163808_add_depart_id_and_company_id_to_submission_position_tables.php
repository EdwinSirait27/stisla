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
        $table->uuid('company_id')->nullable();
          $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')->onDelete('cascade');
        $table->uuid('department_id')->nullable();
              $table->foreign('department_id')
                ->references('id')
                ->on('departments_tables')->onDelete('cascade');
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
