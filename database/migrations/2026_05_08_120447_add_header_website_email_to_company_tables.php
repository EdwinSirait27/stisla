<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Livewire\after;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_tables', function (Blueprint $table) {
            $table->string('header')->nullable()->after('name');
            $table->string('website')->nullable()->after('header');
            $table->string('email')->nullable()->after('website');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_tables', function (Blueprint $table) {
            //
        });
    }
};
