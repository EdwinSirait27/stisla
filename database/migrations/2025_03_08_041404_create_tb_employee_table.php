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
        Schema::create('tb_employee', function (Blueprint $table) {
            $table->id('employee_id'); 
            $table->string('photo')->nullable();
            $table->string('name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('religion', ['Islam', 'Protestant', 'Catholic', 'Hindu', 'Buddhist', 'Confucianism'])->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('employee_status', ['GM', 'Manager', 'AG', 'Supervisor', 'Staff', 'DW', 'Store Staff'])->nullable();
            $table->enum('division', ['IT', 'Buyer', 'Warehouse', 'Finance', 'HR', 'Staff', 'Store Staff'])->nullable();
            $table->string('national_id')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('last_education')->nullable();
            $table->date('graduation_year')->nullable();
            $table->string('major')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable(); 
            $table->string('email')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('tb_employee');
    }
};
