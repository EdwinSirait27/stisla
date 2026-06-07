<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
               $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';  
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('payroll_period_id')->nullable();

            // Periode
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->date('period_start');
            $table->date('period_end');

            // Absensi
            $table->integer('working_days')->default(0);
            $table->integer('attendance_days')->default(0);
            $table->integer('absent_days')->default(0);

            // Prorate
            $table->boolean('is_prorate')->default(false);
            $table->integer('prorate_days')->nullable();
            $table->decimal('prorate_ratio', 8, 4)->nullable();

            // Salary snapshot
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('position_allowance', 15, 2)->default(0);
            $table->decimal('daily_rate', 15, 2)->default(0);

            // Income tambahan
            $table->decimal('overtime_amount', 15, 2)->default(0);
            $table->decimal('reimburse_amount', 15, 2)->default(0);

            // Gross
            $table->decimal('gross_salary', 15, 2)->default(0);

            // Totals
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                  ->references('id')->on('employees_tables')
                  ->onDelete('cascade');

            $table->foreign('payroll_period_id')
                  ->references('id')->on('payroll_periods')
                  ->onDelete('set null');
            $table->foreign('approved_by')
                  ->references('id')->on('employees_tables')
                  ->onDelete('set null');

            $table->unique(['employee_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};