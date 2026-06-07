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
        Schema::create('employee_salaries', function (Blueprint $table) {
               $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';  
           $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('position_allowance', 15, 2)->default(0);
            $table->decimal('daily_rate', 15, 2)->default(0);
            $table->date('effective_date');
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                  ->references('id')->on('employees_tables')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')->on('employees_tables')
                  ->onDelete('set null');

            $table->unique(['employee_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
