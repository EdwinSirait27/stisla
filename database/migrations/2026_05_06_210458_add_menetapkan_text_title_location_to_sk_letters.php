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
        Schema::table('sk_letters', function (Blueprint $table) {
            $table->text('title')->nullable()->before('sk_number');
            $table->string('location')->default('Denpasar')->after('inactive_date');
            $table->text('menetapkan_text')->nullable()->before('notes');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sk_letters', function (Blueprint $table) {
            //
        });
    }
};
