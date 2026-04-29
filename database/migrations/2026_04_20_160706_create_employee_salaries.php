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
           $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->enum('status', ['Monthly', 'Daily'])->default('Monthly');
            // $table->string('basic_salary')->nullable()->change();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->decimal('positional_allowance', 15, 2)->nullable();
            $table->decimal('daily_rate', 15, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
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
