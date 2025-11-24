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
        Schema::create('submission_position_stores_tables', function (Blueprint $table) {
            $table->uuid('submission_position_id');
            $table->uuid('store_id');
            $table->primary(['submission_position_id', 'store_id']);
            $table->foreign('submission_position_id')
                ->references('id')->on('submission_position_tables')
                ->onDelete('cascade');
            $table->foreign('store_id')
                ->references('id')->on('stores_tables')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_position_stores_tables');
    }
};
