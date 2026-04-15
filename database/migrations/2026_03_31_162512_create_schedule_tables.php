<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');

            // Relasi ke roster (shift Pagi/Siang/Malam)
            $table->uuid('roster_id')->nullable();

            $table->date('date');

            // Tipe hari: kerja, libur, cuti, dll
            $table->enum('day_type', ['Work', 'Off', 'Holiday', 'Leave'])->default('Work');

            // Status kehadiran → diupdate otomatis setelah fingerprint recap
            $table->enum('status', ['Scheduled', 'Attended', 'Late', 'Absent'])->default('Scheduled');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_tables')
                ->onDelete('cascade');

            $table->foreign('roster_id')
                ->references('id')
                ->on('roster')
                ->onDelete('set null');

            // 1 karyawan hanya 1 jadwal per hari
            $table->unique(['employee_id', 'date']);
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};