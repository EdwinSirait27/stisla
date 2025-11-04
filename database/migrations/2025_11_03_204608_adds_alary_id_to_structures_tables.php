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
        Schema::table('structures_tables', function (Blueprint $table) {
        $table->uuid('salary_id')->nullable();
           $table->foreign('salary_id')
               ->references('id')
                ->on('salary_tables')->onDelete('cascade');
            
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('structures_tables', function (Blueprint $table) {
            //
        });
    }
};
