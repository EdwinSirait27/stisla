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
        Schema::create('employee_overtime_rates', function (Blueprint $table) {
           $table->uuid('id')->primary();
    $table->uuid('employee_id')->nullable();
    $table->decimal('rate_per_hour', 12, 2)->default(0);
    $table->timestamps();
    $table->foreign('employee_id')->references('id')->on('employees_tables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_overtime_rates');
    }
};
