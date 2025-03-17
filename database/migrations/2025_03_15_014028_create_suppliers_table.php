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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');                          // Nama pemasok
            $table->string('contact_name')->nullable();      // Nama kontak
            $table->string('phone')->nullable();             // No telepon
            $table->string('email')->nullable();             // Email
            $table->text('address')->nullable();             // Alamat
            $table->boolean('is_active')->default(true);     // Status aktif
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
        Schema::dropIfExists('suppliers');
    }
};
