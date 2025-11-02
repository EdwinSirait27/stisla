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
        Schema::create('submission_position_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->string('position_name')->nullable();
            $table->string('role_summary')->nullable();
            $table->string('key_respon')->nullable();
            $table->string('qualifications')->nullable();
            $table->string('work_location')->nullable();
            $table->string('reason_reject')->nullable();
            $table->set('type', ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'])->nullable();
            $table->enum('status', ['Pending','On review','Reject','Accepted'])->nullable();
            $table->string('notes')->nullable();
            $table->uuid('approver_1')->nullable();
            $table->uuid('approver_2')->nullable();
            $table->timestamps();
             $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
                 $table->foreign('approver_1')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
                 $table->foreign('approver_2')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_position_tables');
    }
};
