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
            $table->uuid('manager_id')->nullable()->after('phone_num');
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
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
