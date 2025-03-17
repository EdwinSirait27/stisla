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
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_member')->nullable();      // Alamat toko
            $table->enum('member',['Bronze','Silver','Gold','Guide'])->nullable();        // Telepon toko
            $table->string('name')->nullable();        // Email toko
            $table->string('address');   // Persentase pajak
            $table->string('telph')->nullable();               // Logo toko
            $table->string('point')->nullable();        // Mata uang
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
        Schema::dropIfExists('members');
    }
};
