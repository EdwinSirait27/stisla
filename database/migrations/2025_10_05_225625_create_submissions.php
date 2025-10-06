<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->uuid('approver_id')->nullable();
            $table->enum('type', ['Annual Leave', 'Sick Leave', 'Overtime', 'Maternity Leave'])->nullable();
            $table->date('leave_date_from')->nullable();
            $table->date('leave_date_to')->nullable();
            $table->string('duration')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
            $table->foreign('approver_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
