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
        Schema::table('payrolls_tables', function (Blueprint $table) {
            $table->string('daily_allowance')->nullable()->change();
            $table->string('house_allowance')->nullable()->change();
            $table->string('meal_allowance')->nullable()->change();
            $table->string('transport_allowance')->nullable()->change();
            $table->string('bonus')->nullable()->change();
            $table->string('overtime')->nullable()->change();
            $table->string('salary')->nullable()->change();
            $table->string('late_fine')->nullable()->change();
            $table->string('punishment')->nullable()->change();
            $table->string('period')->nullable()->change();
            $table->string('tax')->nullable()->change();
            $table->string('bpjs_kes')->nullable()->change();
            $table->string('bpjs_ket')->nullable()->change();
            $table->string('debt')->nullable()->change();
            $table->string('deductions')->nullable()->change();
            $table->string('take_home')->nullable()->change();
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
