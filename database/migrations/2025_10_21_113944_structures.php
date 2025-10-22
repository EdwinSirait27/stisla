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
        Schema::create('structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('store_id')->nullable();
            $table->string('structure_code')->nullable();
            $table->string('structure_name')->nullable();
            $table->boolean('is_manager_store')->default(false)->nullable();
            $table->boolean('is_manager_department')->default(false)->nullable();
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')->onDelete('cascade');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments_tables')->onDelete('cascade');
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
