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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();                   // Nomor faktur
            $table->uuid('user_id')->constrained();                  // ID kasir
            $table->string('customer_name')->nullable();                  // Nama pelanggan
            $table->decimal('total_amount', 12, 2);                       // Total harga sebelum diskon
            $table->decimal('discount', 12, 2)->default(0);               // Diskon
            $table->decimal('tax', 12, 2)->default(0);                    // Pajak
            $table->decimal('final_amount', 12, 2);                       // Total akhir
            $table->enum('payment_method', ['tunai', 'kartu', 'transfer', 'qris'])->default('tunai'); // Metode pembayaran
            $table->enum('payment_status', ['lunas', 'tertunda', 'sebagian'])->default('lunas');      // Status pembayaran
            $table->decimal('amount_paid', 12, 2);                        // Jumlah yang dibayar
            $table->decimal('change_amount', 12, 2)->default(0);          // Kembalian
            $table->text('notes')->nullable();                            // Catatan
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
        Schema::dropIfExists('sales');
    }
};
