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
        Schema::table('submission_position_tables', function (Blueprint $table) {
        $table->uuid('store_id')->nullable();
        $table->uuid('position_id')->nullable();  
            $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')->onDelete('cascade');
            $table->foreign('position_id')
                ->references('id')
                ->on('position_tables')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_position_tables', function (Blueprint $table) {
            //
        });
    }
};
