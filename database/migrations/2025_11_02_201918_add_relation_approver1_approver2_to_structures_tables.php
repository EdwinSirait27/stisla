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
        Schema::table('structures_tables', function (Blueprint $table) {
            $table->foreign('approval_1')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
            $table->foreign('approval_2')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('structures_tables', function (Blueprint $table) {
            //
        });
    }
};
