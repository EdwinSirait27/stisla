<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
    public function up()
    {
        Schema::create('fingerprints_recap', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('pin',32);       // PIN -> match ke att_log.pin & employees_tables.pin

            $table->date('date');
            $table->time('time_in')->nullable();    // Scan pertama inoutmode=1
            $table->time('time_out')->nullable();   // Scan terakhir inoutmode=2
            $table->integer('duration_minutes')->nullable();
            $table->string('device_sn', 30)->nullable(); // <att_log.sn
            $table->enum('sync_status', ['Synced', 'Pending', 'Error'])->default('Synced');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->onDelete('cascade');


            // 1 karyawan hanya 1 recap per hari
            $table->unique(['employee_id', 'date']);
            $table->index('date');
            $table->index('pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('fingerprints_recap');
    }
};
