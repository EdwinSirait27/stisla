<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('two_factor_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
        $table->string('user_id');
        $table->enum('event', ['enabled', 'disabled', 'verified', 'failed', 'recovery_used']);
        $table->string('ip_address', 45)->nullable();
        $table->string('device_type')->nullable();
        $table->timestamps();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_logs');
    }
};
