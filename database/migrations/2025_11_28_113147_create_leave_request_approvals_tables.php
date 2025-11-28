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
        Schema::create('leave_request_approvals_tables', function (Blueprint $table) {
           $table->uuid('id')->primary();

            // Foreign Keys
            $table->uuid('leave_request_id');
            $table->uuid('supervisor_id');

            $table->integer('sequence')->default(1); // urutan approval
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // FK constraints
            $table->foreign('leave_request_id')
                ->references('id')
                ->on('leave_requests_tables')
                ->onDelete('cascade');

            $table->foreign('supervisor_id')
                ->references('id')
                ->on('employees_tables')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_approvals_tables');
    }
};
