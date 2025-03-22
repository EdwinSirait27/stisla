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
        Schema::create('discounts', function (Blueprint $table) {
            $table->uuid('id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['Percentage']);
            $table->decimal('discount_value', 12, 2);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('is_active', ['Active', 'Inactive']);
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
        Schema::dropIfExists('discounts');
    }
};
