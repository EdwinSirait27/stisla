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
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained();                               // ID pemasok
            $table->foreignUuid('user_id')->constrained();                                   // ID pengguna
            $table->string('invoice_number');                                              // Nomor faktur
            $table->decimal('total_amount', 12, 2);                                        // Total
            $table->enum('payment_status', ['lunas', 'tertunda', 'sebagian'])->default('lunas'); // Status pembayaran
            $table->date('purchase_date');                                                 // Tanggal pembelian
            $table->text('notes')->nullable();                                             // Catatan
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
        Schema::dropIfExists('purchases');
    }
};
