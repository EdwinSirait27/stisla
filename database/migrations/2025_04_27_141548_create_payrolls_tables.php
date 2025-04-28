<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->decimal('bonus', 10, 2)->nullable();
            $table->decimal('house_allowance', 10, 2)->nullable();
            $table->decimal('meal_allowance', 10, 2)->nullable();
            $table->decimal('transport_allowance', 10, 2)->nullable();
            $table->decimal('net_salary', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('month_year')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')
               ->references('id')
                ->on('employees_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls_tables');
    }
};
