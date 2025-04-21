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
        Schema::create('fingerprint_devices_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Maintenance'])->nullable()->default('Active');
            $table->timestamps();
            $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')
                ->onDelete('cascade');
        });

    }

    /**>
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fingerprint_devices_tables');
    }
};
