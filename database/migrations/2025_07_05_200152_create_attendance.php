<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id')->nullable();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->time('jam_masuk2')->nullable();
            $table->time('jam_keluar2')->nullable();
            $table->time('jam_masuk3')->nullable();
            $table->time('jam_keluar3')->nullable();
            $table->time('jam_masuk4')->nullable();
            $table->time('jam_keluar4')->nullable();
            $table->time('jam_masuk5')->nullable();
            $table->time('jam_keluar5')->nullable();
            $table->time('jam_masuk6')->nullable();
            $table->time('jam_keluar6')->nullable();
            $table->time('jam_masuk7')->nullable();
            $table->time('jam_keluar7')->nullable();
            $table->time('jam_masuk8')->nullable();
            $table->time('jam_keluar8')->nullable();
            $table->time('jam_masuk9')->nullable();
            $table->time('jam_keluar9')->nullable();
            $table->time('jam_masuk10')->nullable();
            $table->time('jam_keluar10')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};
