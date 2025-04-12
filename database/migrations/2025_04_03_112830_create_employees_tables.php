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
        Schema::create('employees_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_name')->unique()->nullable();
            $table->uuid('position_id')->nullable();
            $table->uuid('store_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->date('join_date')->nullable();
            $table->text('lenght_of_service')->nullable();
            $table->enum('marriage', ['Yes','No'])->nullable();
            $table->enum('child', ['0','1','2','3','4','5'])->nullable();
            $table->string('telp_number')->nullable();
            $table->string('nik')->nullable();
            $table->enum('gender', ['Male','Female','MD'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('biological_mother_name')->nullable();
            $table->enum('religion', ['Catholic Christian','Christian','Islam','Hindu','Confucian','Buddha'])->nullable();
            $table->text('current_address')->nullable();
            $table->string('id_card_address')->nullable();
            $table->enum('last_education', ['Elementary School','Junior High School','Senior High School','Diploma','Bachelor Degree'])->nullable();
            $table->string('institution')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bpjs_kes')->nullable();
            $table->string('bpjs_ket')->nullable();
            $table->string('email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->decimal('house_allowance', 12, 2)->nullable();
            $table->decimal('meal_allowance', 12, 2)->nullable();
            $table->decimal('transport_allowance', 12, 2)->nullable();
            $table->decimal('total_salary', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees_tables');
    }
};
