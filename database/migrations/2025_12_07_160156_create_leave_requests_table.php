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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('leave_balance_id');  // FK ke Leavebalance
            $table->date('start_date');
            $table->date('end_date');
            $table->text('employee_reason')->nullable();
            $table->text('approver_reason')->nullable();
            $table->enum('status', ['Pending','Sent', 'Approved', 'Rejected','Accepted'])->default('Pending');
            $table->uuid('approved_by')->nullable(); // employee yg approve
            $table->timestamps();
            $table->foreign('leave_balance_id')->references('id')->on('leave_balances_tables');
            $table->foreign('approved_by')->references('id')->on('employees_tables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
