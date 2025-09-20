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
        Schema::create('announcements', function (Blueprint $table) {
           $table->uuid('id')->primary();
           $table->uuid('user_id')->nullable();
          $table->string('title')->nullable();       // judul pengumuman
        $table->longText('content')->nullable();   // isi pengumuman (pakai text editor)
        $table->date('publish_date')->nullable(); // kapan dipublish
        $table->timestamps();
         $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
