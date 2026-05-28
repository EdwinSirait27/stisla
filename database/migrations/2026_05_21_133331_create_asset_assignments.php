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
        Schema::create('asset_assignments', function (Blueprint $table) {
             $table->charset = 'utf8mb4';                    // ← tambah ini
    $table->collation = 'utf8mb4_unicode_ci';       // ← tambah ini
            $table->uuid('id')->primary();
            $table->uuid('asset_id')->nullable();      
            $table->foreign('asset_id')
                ->references('id')
                ->on('assets')
                ->cascadeOnDelete();
            $table->uuid('employee_id')->nullable();    
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->uuid('assigned_by')->nullable();    
            $table->foreign('assigned_by')
                ->references('id')
                ->on('employees_tables')
                ->cascadeOnDelete();
            $table->date('assigned_date')->nullable();
            $table->date('returned_date')->nullable();  // null = masih dipegang
            $table->enum('status', ['Active', 'Returned'])->default('Active');
            $table->text('notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
