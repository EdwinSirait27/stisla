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
        Schema::create('employee_atasan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('atasan_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->foreign('atasan_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->unique(['employee_id', 'atasan_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_atasan');
    }
};
