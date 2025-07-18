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
        Schema::create('edited_fingerprints', function (Blueprint $table) {
           $table->id();
    $table->string('pin');
    $table->string('employee_name');
    $table->string('position_name')->nullable();
    $table->string('device_name')->nullable();
    $table->date('scan_date');
    for ($i = 1; $i <= 10; $i++) {
        $table->dateTime('in_' . $i)->nullable();
    }
    $table->string('duration')->nullable();
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
        Schema::dropIfExists('edited_fingerprints');
    }
};
