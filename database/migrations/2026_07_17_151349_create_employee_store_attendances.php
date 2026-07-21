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
        Schema::create('employee_store_attendances', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('employee_store_id'); // FK ke employee_stores.id
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('employee_store_id')->references('id')->on('employee_stores')->onDelete('cascade');

            // Satu employee tidak boleh duplikat store yang sama
            $table->unique(['employee_id', 'employee_store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_store_attendances');
    }
};
