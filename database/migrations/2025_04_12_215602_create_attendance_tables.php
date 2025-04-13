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
        Schema::create('attendance_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->date('attendance_date')->nullable();;
            $table->enum('status', ['Late', 'Ontime', 'Absent'])->nullable();
            $table->string('device_id')->nullable()->comment('ID perangkat Fingerspot');
            $table->boolean('is_public_holiday')->default(false)->nullable();;
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Index untuk pencarian cepat
            $table->index('attendance_date');
            $table->index(['user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_tables');
    }
};
