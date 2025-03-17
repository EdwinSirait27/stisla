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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained();      // ID kategori
            $table->string('name');                               // Nama produk
            $table->text('description')->nullable();              // Deskripsi produk
            $table->decimal('price', 12, 2);                      // Harga jual
            $table->decimal('cost_price', 12, 2);                 // Harga modal
            $table->integer('stock')->default(0);                 // Jumlah stok
            $table->string('barcode')->unique()->nullable();      // Kode barcode produk
            $table->string('sku')->unique()->nullable();          // Kode SKU
            $table->string('image')->nullable();                  // Gambar produk
            $table->boolean('is_active')->default(true);          // Status aktif
            $table->boolean('track_stock')->default(true);        // Tracking stok
            $table->integer('min_stock')->default(10);            // Stok minimum 
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
        Schema::dropIfExists('products');
    }
};
