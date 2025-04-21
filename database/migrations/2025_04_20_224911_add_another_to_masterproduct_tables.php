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
        Schema::table('masterproduct_tables', function (Blueprint $table) {
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands_tables')->onDelete('restrict'); 
            $table->foreign('category_id')
                ->references('id')
                ->on('categories_tables')->onDelete('restrict'); 
            $table->foreign('uom_id')
                ->references('id')
                ->on('uoms_tables')->onDelete('restrict'); 
            $table->foreign('taxstatus_id')
                ->references('id')
                ->on('taxstatus_tables')->onDelete('restrict'); 
            $table->foreign('statusproduct_id')
                ->references('id')
                ->on('statusproduct_tables')->onDelete('restrict'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masterproduct_tables', function (Blueprint $table) {
            //
        });
    }
};
