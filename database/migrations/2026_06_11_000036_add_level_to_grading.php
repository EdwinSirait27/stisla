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
        Schema::table('grading', function (Blueprint $table) {
            $table->enum('level', ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20'])->nullable()->after('grading_name');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grading', function (Blueprint $table) {
            //
        });
    }
};
