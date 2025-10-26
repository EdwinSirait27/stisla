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
            $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')->onDelete('cascade');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments_tables')->onDelete('cascade');
            $table->foreign('position_id')
                ->references('id')
                ->on('position_tables')->onDelete('cascade');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')->onDelete('cascade');
            $table->foreign('parent_id')
                ->references('id')
                ->on('structures_tables')->nullOnDelete();
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
