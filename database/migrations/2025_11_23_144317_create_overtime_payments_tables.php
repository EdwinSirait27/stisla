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
        Schema::create('overtime_payments_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('overtime_submission_id');
            $table->uuid('employee_id');

            $table->decimal('total_hours', 8, 2);
            $table->decimal('hourly_rate', 12, 2);
            $table->decimal('multiplier', 8, 2)->default(1.5);
            $table->decimal('amount', 20, 2);

            $table->uuid('payroll_period_id')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('overtime_submission_id')->references('id')->on('overtime_submissions_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_payments_tables');
    }
};
