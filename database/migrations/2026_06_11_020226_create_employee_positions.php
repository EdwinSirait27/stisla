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
        Schema::create('employee_positions', function (Blueprint $table) {
        $table->charset = 'utf8mb4';        
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('position_id')->nullable();
            $table->uuid('employee_id')->nullable();
            $table->foreign('position_id')
                ->references('id')->on('position_tables')
                ->onDelete('cascade');
            $table->foreign('employee_id')
                ->references('id')->on('employees_tables')
                ->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->unique(['employee_id', 'position_id']);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
    }
};
