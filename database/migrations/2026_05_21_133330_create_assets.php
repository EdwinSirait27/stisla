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
        Schema::create('assets', function (Blueprint $table) {
              $table->charset = 'utf8mb4';                    // ← tambah ini
    $table->collation = 'utf8mb4_unicode_ci';       // ← tambah ini
         $table->uuid('id')->primary();
$table->uuid('asset_category_id')->nullable();    
  $table->foreign('asset_category_id')
      ->references('id')
      ->on('asset_categories')
      ->cascadeOnDelete();
$table->string('asset_name')->nullable();
$table->string('serial_number')->nullable();
$table->string('brand')->nullable();
$table->string('model')->nullable();
$table->date('purchase_date')->nullable();
$table->decimal('purchase_price', 15, 2)->nullable();
$table->enum('status', ['Active', 'Damaged', 'Lost', 'Retired'])->default('Active');
$table->text('notes')->nullable();
$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
