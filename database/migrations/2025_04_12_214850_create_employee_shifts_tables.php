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
        Schema::create('employee_shifts_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->date('date')->nullable();
            $table->enum('status', ['Scheduled', 'Cancelled', 'Swapped'])->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('shift_id')
                  ->references('id')
                  ->on('shifts_tables')
                  ->onDelete('cascade');
            
            // Composite unique key to prevent duplicate entries
            $table->unique(['user_id', 'shift_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_shifts_tables');
    }
};
