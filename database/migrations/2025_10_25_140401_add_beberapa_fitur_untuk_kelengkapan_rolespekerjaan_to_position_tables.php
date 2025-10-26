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
        Schema::table('position_tables', function (Blueprint $table) {
              $table->enum('status', ['Active','Pending','Inactive','Reject','On Review'])->nullable();
            $table->string('reason_reject')->nullable();
            $table->uuid('approval_1')->nullable();
            $table->uuid('approval_2')->nullable();
            $table->string('role_summary')->nullable();
            $table->string('key_respon')->nullable();
            $table->string('qualifications')->nullable();
            $table->string('work_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_tables', function (Blueprint $table) {
            //
        });
    }
};
