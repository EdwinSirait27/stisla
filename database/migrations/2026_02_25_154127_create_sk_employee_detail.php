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
         Schema::create('sk_employee_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Relasi ke header SK
            $table->uuid('sk_employee_id')->nullable();
            $table->foreign('sk_employee_id')
                ->references('id')
                ->on('sk_employee')
                ->cascadeOnDelete();
            // Karyawan yang terkena SK
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            // Perubahan posisi (opsional tergantung jenis SK)
            $table->uuid('old_position_id')->nullable();
            $table->uuid('new_position_id')->nullable();
            $table->foreign('old_position_id')
                ->references('id')->on('position_tables')
                ->nullOnDelete();
            $table->foreign('new_position_id')
                ->references('id')->on('position_tables')
                ->nullOnDelete();
            $table->uuid('old_department_id')->nullable();
            $table->uuid('new_department_id')->nullable();
            $table->foreign('old_department_id')
                ->references('id')->on('departments_tables')
                ->nullOnDelete();
            $table->foreign('new_department_id')
                ->references('id')->on('departments_tables')
                ->nullOnDelete();
            $table->uuid('old_company_id')->nullable();
            $table->uuid('new_company_id')->nullable();
            $table->foreign('old_company_id')
                ->references('id')->on('company_tables')
                ->nullOnDelete();
            $table->foreign('new_company_id')
                ->references('id')->on('company_tables')
                ->nullOnDelete();
            $table->decimal('old_salary', 15, 2)->nullable();
            $table->decimal('new_salary', 15, 2)->nullable();

            $table->text('notes')->nullable();
            // Tanggal efektif khusus jika beda dari header
            $table->date('effective_date')->nullable();
            $table->timestamps();
            // Satu employee tidak boleh dobel dalam satu SK
            $table->unique(['sk_employee_id', 'user_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_employee_detail');
    }
};
