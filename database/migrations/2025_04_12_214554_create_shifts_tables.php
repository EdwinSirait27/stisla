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
        Schema::create('shifts_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
                $table->uuid('store_id')->nullable();
                $table->string('shift_name')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamp('last_sync')->nullable();
                $table->boolean('is_holiday')->default(false);  
                $table->timestamps();
                $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts_tables');
    }
};
