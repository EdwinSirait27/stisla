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
          Schema::create('ph', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['Hindu','Non Hindu','All'])->nullable();
            $table->date('date')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
