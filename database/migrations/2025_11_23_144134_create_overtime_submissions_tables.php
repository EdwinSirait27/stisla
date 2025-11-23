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
        Schema::create('overtime_submissions_tables', function (Blueprint $table) {
           $table->uuid('id')->primary();
            $table->uuid('employee_id');

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_hours', 8, 2);

            $table->text('reason')->nullable();

            $table->enum('compensation_type', ['Toil', 'Cash']);

            $table->string('status')->default('pending');
            $table->uuid('approver_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('employees_tables')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_submissions_tables');
    }
};
