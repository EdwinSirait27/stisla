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
        Schema::table('sk_type', function (Blueprint $table) {
            $table->enum('categories', ['Employment','Mutation','Payroll','Disciplinary','Termination'])->nullable()->after('sk_name');
            $table->boolean('affects_salary')->default(false)->after('categories');
            $table->boolean('affects_position')->default(false)->after('affects_salary');
            $table->boolean('affects_status')->default(false)->after('affects_position');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sk_type', function (Blueprint $table) {
        });
    }
};
