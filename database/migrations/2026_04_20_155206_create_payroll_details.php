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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->integer('period_month')->nullable(); // 1-12
            $table->integer('period_year')->nullable();
            $table->decimal('gross_salary', 15, 2)->nullable();
            $table->decimal('total_deduction', 15, 2)->nullable();
            $table->decimal('total_income', 15, 2)->nullable();
            $table->decimal('net_salary', 15, 2)->nullable();
            $table->decimal('take_home', 15, 2)->nullable();
            $table->enum('status', ['Draft', 'Processed', 'Paid'])->default('Draft');
            $table->timestamps();
            // FK
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->unique(['employee_id', 'period_month', 'period_year'], 'unique_payroll_per_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
