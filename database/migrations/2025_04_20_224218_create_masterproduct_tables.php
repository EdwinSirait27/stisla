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
        Schema::create('masterproduct_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('plu')->unique()->nullable();
            $table->string('description')->nullable();
            $table->text('long_description')->nullable();
            $table->uuid('brand_id')->nullable(); 
            $table->uuid('category_id')->nullable(); 
            $table->uuid('uom_id')->nullable(); 
            $table->uuid('taxstatus_id')->nullable(); 
            $table->uuid('statusproduct_id')->nullable(); 
            $table->integer('good_stock')->nullable();
            $table->integer('bad_stock')->nullable();
            $table->decimal('cogs', 12, 2)->nullable();
            $table->decimal('retailprice', 12, 2)->nullable();
            $table->decimal('memberbronzeprice', 12, 2)->nullable();
            $table->decimal('membersilverprice', 12, 2)->nullable();
            $table->decimal('membergoldprice', 12, 2)->nullable();
            $table->decimal('memberplatinumprice', 12, 2)->nullable();
            $table->integer('min_stock')->nullable();
            $table->integer('max_stock')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masterproduct_tables');
    }
};
