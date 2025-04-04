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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('device_lan_mac', 100);
            $table->string('device_wifi_mac', 100);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('device_lan_mac');
            $table->unique('device_wifi_mac');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('terms_id');
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('remember_token')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('terms_id')
                ->references('id')
                ->on('terms')->onDelete('cascade');
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('activity_type'); // Misalnya: 'login', 'logout'
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('activity_time')->useCurrent();
            $table->string('device_lan_mac')->nullable();
            $table->string('device_wifi_mac')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_id')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
};
