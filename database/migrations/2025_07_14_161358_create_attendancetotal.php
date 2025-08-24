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
        Schema::create('attendancetotal', function (Blueprint $table) {
            $table->id();
                $table->unsignedBigInteger('attendance_id')->index(); 
                $table->date('month')->nullable(); 
                $table->string('total')->nullable(); 
            $table->timestamps();
          $table->foreign('attendance_id')
                ->references('id')
                ->on('attendance')->onDelete('cascade');
        });
        
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendancetotal');
    }
};

