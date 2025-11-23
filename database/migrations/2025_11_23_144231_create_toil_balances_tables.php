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
        Schema::create('toil_balances_tables', function (Blueprint $table) {
          $table->uuid('id')->primary();

            $table->uuid('employee_id');
            $table->uuid('overtime_submission_id');

            $table->decimal('earned_hours', 8, 2);
            $table->decimal('used_hours', 8, 2)->default(0);

            $table->date('expires_at')->nullable();

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
        Schema::dropIfExists('toil_balances_tables');
    }
};
