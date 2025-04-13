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
        Schema::create('public_holidays_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('holiday_name')->nullable();
            $table->date('date')->nullable();
            $table->smallInteger('year')->nullable();
            $table->boolean('is_recurring')->default(true)->nullable();;
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Index untuk pencarian cepat
            $table->index('date');
            $table->index('year');
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_holidays_tables');
    }
};
