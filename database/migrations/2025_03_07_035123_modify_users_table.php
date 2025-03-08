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
            $table->dropUnique(['email']); 
            $table->renameColumn('email', 'Username'); 

            $table->enum('Role', ['SU', 'GM', 'HR','Gudang','Kepala Gudang','Kepala Buyer','Buyer','Finance','Kepala Finance']);
        });
    }

    public function down()
    {

    }
};