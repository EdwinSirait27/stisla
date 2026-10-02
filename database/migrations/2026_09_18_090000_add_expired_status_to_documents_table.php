<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft', 'issued', 'revoked', 'expired') NOT NULL DEFAULT 'draft'");

        Schema::table('documents', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('issued_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });

        DB::statement("ALTER TABLE documents MODIFY status ENUM('draft', 'issued', 'revoked') NOT NULL DEFAULT 'draft'");
    }
};
