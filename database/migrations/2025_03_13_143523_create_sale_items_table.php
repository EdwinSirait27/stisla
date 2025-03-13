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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
    $table->foreignId('sale_id')->constrained()->onDelete('cascade'); // ID penjualan
    $table->foreignId('product_id')->constrained();                   // ID produk
    $table->integer('quantity');                                      // Jumlah
    $table->decimal('price', 12, 2);                                  // Harga satuan
    $table->decimal('discount', 12, 2)->default(0);                   // Diskon per item
    $table->decimal('subtotal', 12, 2);                               // Subtotal
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
        Schema::dropIfExists('sale_items');
    }
};
