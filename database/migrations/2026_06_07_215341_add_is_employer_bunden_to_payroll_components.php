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
        Schema::table('payroll_components', function (Blueprint $table) {
               $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';  
                        $table->boolean('is_employer_burden')->default(false)->after('is_fixed');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            //
        });
    }
};
