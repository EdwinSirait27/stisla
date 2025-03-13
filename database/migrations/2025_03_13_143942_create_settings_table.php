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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
    $table->string('store_name');                     // Nama toko
    $table->string('store_address')->nullable();      // Alamat toko
    $table->string('store_phone')->nullable();        // Telepon toko
    $table->string('store_email')->nullable();        // Email toko
    $table->string('tax_percentage')->default('0');   // Persentase pajak
    $table->string('logo')->nullable();               // Logo toko
    $table->string('currency')->default('Rp');        // Mata uang
    $table->text('footer_text')->nullable();          // Teks footer struk
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
        Schema::dropIfExists('settings');
    }
};
