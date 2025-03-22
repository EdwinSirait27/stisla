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
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();                   
            $table->decimal('total_amount', 12, 2);                 
            $table->decimal('discount', 12, 2)->default(0);  
            $table->decimal('tax', 12, 2)->default(0);       
            $table->decimal('final_amount', 12, 2);                 
            $table->enum('payment_method', ['cash', 'transfer', 'qris']); 
            $table->enum('payment_status', ['paid', 'hold', 'cancel']);      
            $table->decimal('amount_paid', 12, 2);                     
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('sales');
    }
};
