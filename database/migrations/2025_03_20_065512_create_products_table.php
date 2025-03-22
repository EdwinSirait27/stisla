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
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 12, 2);
    $table->decimal('cost_price', 12, 2);
    $table->integer('stock')->default(0);
    $table->string('barcode')->unique()->nullable();
    $table->string('sku')->unique()->nullable();
    $table->string('image')->nullable();
    $table->enum('is_active', ['Active', 'Inactive']);
    $table->boolean('track_stock')->default(true);
    $table->integer('min_stock')->default(10);
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
