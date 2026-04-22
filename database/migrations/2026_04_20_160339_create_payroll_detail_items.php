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
        Schema::create('payroll_detail_items', function (Blueprint $table) {
          $table->uuid('id')->primary();

            $table->uuid('payroll_detail_id')->nullable();
            $table->uuid('payroll_component_id')->nullable();
            $table->string('amount')->nullable()->change();
            $table->timestamps();
            // FK
            $table->foreign('payroll_detail_id')
                ->references('id')
                ->on('payroll_details')
                ->cascadeOnDelete();
            $table->foreign('payroll_component_id')
                ->references('id')
                ->on('payroll_components')
                ->cascadeOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_detail_items');
    }
};
