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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
    $table->string('store_name');                     
    $table->string('store_address')->nullable();      
    $table->string('store_phone')->nullable();        
    $table->string('store_email')->nullable();        
    $table->string('tax_percentage')->default('0');
    $table->string('logo')->nullable();               
    $table->string('currency')->default('Rp'); 
    $table->text('footer_text')->nullable();          
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
        Schema::dropIfExists('settings');
    }
};
