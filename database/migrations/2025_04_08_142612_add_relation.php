<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('terms_id')
                ->references('id')
                ->on('terms')->onDelete('cascade');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
        });
        Schema::table('departments_tables', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
        });
        Schema::table('employees_tables', function (Blueprint $table) {
            $table->foreign('position_id')
                ->references('id')
                ->on('position_tables')->onDelete('cascade');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')->onDelete('cascade');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments_tables')->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
