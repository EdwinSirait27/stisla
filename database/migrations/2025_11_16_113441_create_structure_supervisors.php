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
       Schema::create('structure_supervisors', function (Blueprint $table) {
            $table->uuid('structure_id');
            $table->uuid('supervisor_id');
            $table->timestamps();
            $table->primary(['structure_id', 'supervisor_id']);
            $table->foreign('structure_id')
                ->references('id')
                ->on('structures_tables')
                ->cascadeOnDelete();

            $table->foreign('supervisor_id')
                ->references('id')
                ->on('structures_tables')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_supervisors');
    }
};
