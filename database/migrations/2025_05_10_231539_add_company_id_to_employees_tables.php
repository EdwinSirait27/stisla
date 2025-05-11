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
        Schema::table('employees_tables', function (Blueprint $table) {
            $table->uuid('company_id')->nullable()->after('store_id');
            $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees_tables', function (Blueprint $table) {
            //
        });
    }
};
