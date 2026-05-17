<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sk_menimbang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sk_letter_id')->nullable();
            $table->text('content_menimbang')->nullable();
            $table->string('order_no')->nullable();
            $table->foreign('sk_letter_id')
      ->references('id')
      ->on('sk_letters')
      ->cascadeOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_menimbang');
    }
};
