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
         Schema::create('structure', function (Blueprint $table) {
           $table->uuid('id')->primary();
           $table->uuid('employee_id')->nullable();
          $table->uuid('level_id')->nullable();   
          $table->boolean('is_manager')->default(false);
           $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
                 $table->foreign('level_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
