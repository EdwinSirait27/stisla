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
        Schema::create('contract', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->uuid('structure_id');
            $table->foreign('structure_id')
                ->references('id')
                ->on('structures_tables')
                ->cascadeOnDelete();
            $table->enum('contract_type', ['PKWT', 'On Job Training', 'DW']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->decimal('positional_allowance', 15, 2)->nullable();
            $table->decimal('daily_rate', 15, 2)->nullable();
            $table->enum('contract_status', ['Active', 'Expired', 'Terminated'])
                ->default('Active');
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'contract_status']);
            $table->index('structure_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract');
    }
};
