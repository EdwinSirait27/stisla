<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_letter_employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sk_letter_id');
            $table->uuid('employee_id');
            // Snapshot jabatan sebelum dan sesudah SK
            $table->uuid('previous_structure_id')->nullable();
            $table->uuid('new_structure_id')->nullable();
            // Snapshot data karyawan saat SK diterbitkan
            $table->uuid('company_id')->nullable();
            $table->uuid('position_id')->nullable();
            $table->uuid('group_id')->nullable();
            $table->uuid('grading_id')->nullable();
            $table->uuid('department_id')->nullable();
             $table->decimal('basic_salary', 15, 2)->nullable();
    $table->decimal('positional_allowance', 15, 2)->nullable();
    $table->decimal('daily_rate', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            // Foreign keys
            $table->foreign('sk_letter_id')
                  ->references('id')
                  ->on('sk_letters')
                  ->cascadeOnDelete();
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees_tables')
                  ->cascadeOnDelete();
            $table->foreign('company_id')
                  ->references('id')
                  ->on('employees_tables')
                  ->cascadeOnDelete();
            $table->foreign('previous_structure_id')
                  ->references('id')
                  ->on('structures_tables')
                  ->nullOnDelete();
            $table->foreign('new_structure_id')
                  ->references('id')
                  ->on('structures_tables')
                  ->nullOnDelete();
            $table->foreign('position_id')
                  ->references('id')
                  ->on('position_tables')          // sesuaikan nama tabel
                  ->nullOnDelete();
            $table->foreign('group_id')
                  ->references('id')
                  ->on('groups_tables')
                  ->nullOnDelete();
            $table->foreign('grading_id')
                  ->references('id')
                  ->on('grading')
                  ->nullOnDelete();
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments_tables')        // sesuaikan nama tabel
                  ->nullOnDelete();

            // Satu karyawan tidak bisa muncul dua kali di SK yang sama
            $table->unique(['sk_letter_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_letter_employees');
    }
};