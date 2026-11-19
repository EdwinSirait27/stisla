<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link reset password sekali pakai yang dikirim admin ke email karyawan.
 * Dikunci per user (bukan per email) karena email ada di employees_tables
 * dan beberapa karyawan bisa memakai email yang sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_password_reset_links', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36)->index();
            $table->char('token_hash', 64)->unique();   // sha256 dari token; token asli hanya ada di email
            $table->string('sent_to');                   // alamat email tujuan saat link dikirim
            $table->char('requested_by', 36)->nullable(); // user admin yang mengirim
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_password_reset_links');
    }
};
