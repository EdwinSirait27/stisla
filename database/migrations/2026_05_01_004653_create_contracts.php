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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')
                ->nullable();
            $table->uuid('sk_letter_id')
                ->nullable();
            $table->uuid('issuer_company_id')
                ->nullable();
            $table->uuid('structure_id')  
                ->nullable();
            $table->uuid('position_id')
                ->nullable();
            $table->uuid('group_id')
                ->nullable();
            $table->uuid('grading_id')
                ->nullable();
            $table->uuid('company_id')
                ->nullable();
            $table->uuid('department_id')
                ->nullable();
            $table->uuid('signed_by_employee')
                ->nullable();
            $table->timestamp('signed_by_employee_at')->nullable();
            $table->enum('contract_type', ['PKWT', 'On Job Training', 'DW'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('contract_status', ['Active', 'Expired', 'Terminated'])
                ->default('Active');
                $table->text('notes')->nullable();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->decimal('positional_allowance', 15, 2)->nullable();
            $table->decimal('daily_rate', 15, 2)->nullable();
            $table->string('file_path')->nullable();
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->nullOnDelete();
            $table->foreign('sk_letter_id')
                ->references('id')
                ->on('sk_letters')
                ->nullOnDelete();
            $table->foreign('issuer_company_id')
                ->references('id')
                ->on('company_tables')
                ->nullOnDelete();
            $table->foreign('structure_id')
                ->references('id')
                ->on('structures_tables')
                ->nullOnDelete();
            $table->foreign('signed_by_employee')
                ->references('id')
                ->on('employees_tables')
                ->nullOnDelete();
            $table->foreign('position_id')
                ->references('id')
                ->on('position_tables')
                ->nullOnDelete();
            $table->foreign('group_id')
                ->references('id')
                ->on('groups_tables')
                ->nullOnDelete();
            $table->foreign('grading_id')
                ->references('id')
                ->on('grading')
                ->nullOnDelete();
            $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')
                ->nullOnDelete();
            $table->foreign('department_id')
                ->references('id')
                ->on('departments_tables')
                ->nullOnDelete();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
