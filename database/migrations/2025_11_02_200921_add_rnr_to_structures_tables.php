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
            $table->string('role_summary')->nullable();
            $table->string('key_respon')->nullable();
            $table->string('qualifications')->nullable();
            $table->string('work_location')->nullable();
            $table->uuid('approval_1')->nullable();
            $table->uuid('approval_2')->nullable();
            $table->string('reason_reject')->nullable();
            $table->enum('submission_status', ['Accepted','Pending','Reject','On Review'])->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('structures_tables', function (Blueprint $table) {
            
        });
    }
};
