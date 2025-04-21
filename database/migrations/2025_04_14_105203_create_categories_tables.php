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
        Schema::create('categories_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category_code')->nullable();
            $table->string('category_name')->nullable();
            $table->timestamps();
        });
            Schema::table('categories_tables', function (Blueprint $table) {
                $table->uuid('parent_id')->nullable()->after('id');
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('categories_tables')
                      ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_tables');
    }
};
