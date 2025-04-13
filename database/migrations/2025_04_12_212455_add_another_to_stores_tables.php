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
        Schema::table('stores_tables', function (Blueprint $table) {
            $table->time('open_time')->before('manager_id')->nullable();
            $table->time('close_time')->after('phone_num')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores_tables', function (Blueprint $table) {
            //
        });
    }
};
