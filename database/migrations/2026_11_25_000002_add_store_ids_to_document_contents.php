<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tempat berita acara dipilih dari daftar lokasi (stores), bisa lebih dari satu.
 * - store_ids: id lokasi yang dipilih (untuk mengisi ulang form saat revisi).
 * - location: nama-nama lokasi digabung koma (dipakai di halaman detail & PDF); diperlebar ke text
 *   karena gabungan beberapa nama bisa melewati 255 karakter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_contents', function (Blueprint $table) {
            $table->json('store_ids')->nullable()->after('event_at');
            $table->text('location')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_contents', function (Blueprint $table) {
            $table->dropColumn('store_ids');
            $table->string('location')->nullable()->change();
        });
    }
};
