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
            $table->integer('total')->default(12)->nullable()->after('is_manager');
            $table->integer('pending')->default(0)->nullable()->after('is_manager');
            $table->integer('approved')->default(0)->nullable()->after('is_manager');
            $table->integer('remaining')->default(0)->nullable()->after('is_manager');
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
