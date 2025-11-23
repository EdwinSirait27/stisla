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
        Schema::create('leave_requests_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->decimal('total_days', 8, 2)->nullable();
            $table->decimal('total_hours', 8, 2)->nullable(); // TOIL only

            $table->text('reason')->nullable();

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->uuid('approver_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types_tables');
            $table->foreign('approver_id')->references('id')->on('employees_tables')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests_tables');
    }
};
