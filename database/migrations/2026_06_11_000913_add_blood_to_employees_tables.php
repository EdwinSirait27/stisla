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
        Schema::table('employees_tables', function (Blueprint $table) {
            $table->enum('blood_type', ['AB+','AB','AB-','A+','A','A-','B+','B','B-','O+','O','O-'])->nullable()->after('child');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees_tables', function (Blueprint $table) {
            //
        });
    }
};
