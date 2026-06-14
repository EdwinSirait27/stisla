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
        Schema::create('employee_atasans', function (Blueprint $table) {
             $table->charset = 'utf8mb4';        
            $table->collation = 'utf8mb4_unicode_ci';
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
            $table->boolean('is_primary')->default(false);
            $table->unique(['employee_id', 'atasan_id']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('employee_atasans');
    }
};
