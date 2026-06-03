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
        Schema::create('roster_settings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';                    // ← tambah ini
            $table->collation = 'utf8mb4_unicode_ci';       // ← tambah ini
                       $table->id();
            $table->unsignedTinyInteger('open_day');   // tanggal buka, misal 20
            $table->unsignedTinyInteger('close_day');  // tanggal tutup, misal 28
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_settings');
    }
};
