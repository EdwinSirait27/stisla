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
    Schema::create('payroll_details', function (Blueprint $table) {
           $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';  
        $table->uuid('id')->primary();
        $table->uuid('payroll_id');
        $table->uuid('payroll_component_id');
        $table->enum('type', ['Income', 'Deduction']);
        $table->decimal('amount', 15, 2)->default(0);
        $table->text('note')->nullable();
        $table->timestamps();

        $table->foreign('payroll_id')
              ->references('id')->on('payrolls')
              ->onDelete('cascade');

        $table->foreign('payroll_component_id')
              ->references('id')->on('payroll_components')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('payroll_details');
}
};
