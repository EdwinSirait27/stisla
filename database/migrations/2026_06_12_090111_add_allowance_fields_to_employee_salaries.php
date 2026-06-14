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
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->decimal('meal_allowance', 15, 2)->default(0)->after('daily_rate');
            $table->decimal('transport_allowance', 15, 2)->default(0)->after('meal_allowance');
            $table->decimal('house_allowance', 15, 2)->default(0)->after('transport_allowance');
            $table->decimal('bpjs_ketenagakerjaan', 15, 2)->default(0)->after('house_allowance');
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0)->after('bpjs_ketenagakerjaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            //
        });
    }
};
