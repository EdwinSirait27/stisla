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
        Schema::create('payroll_periods', function (Blueprint $table) {
               $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';  
            $table->uuid('id')->primary();
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->date('period_start'); // tanggal 26 bulan sebelumnya
            $table->date('period_end');   // tanggal 25 bulan ini
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->uuid('created_by')->nullable();
            $table->uuid('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('note')->nullable();
            $table->foreign('created_by')
                  ->references('id')->on('employees_tables')
                  ->onDelete('set null');
            $table->foreign('locked_by')
                  ->references('id')->on('employees_tables')
                  ->onDelete('set null');
            $table->timestamps();
            $table->unique(['period_month', 'period_year']);
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
