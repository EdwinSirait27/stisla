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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();                               // ID produk
            $table->uuid('user_id')->constrained();                                  // ID pengguna
            $table->enum('adjustment_type', ['tambah', 'kurang', 'koreksi', 'rusak']);    // Tipe penyesuaian
            $table->integer('quantity');                                                  // Jumlah
            $table->integer('stock_before');                                              // Stok sebelum penyesuaian
            $table->integer('stock_after');                                               // Stok setelah penyesuaian
            $table->text('notes')->nullable();                                            // Catatan
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
        Schema::dropIfExists('stock_adjustments');
    }
};
