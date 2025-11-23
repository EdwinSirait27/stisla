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
        Schema::create('leave_types_tables', function (Blueprint $table) {
          $table->uuid('id')->primary();
            $table->string('name')->unique(); // annual, sick, toil, etc
            $table->boolean('is_paid')->default(true);
            $table->decimal('default_balance', 8, 2)->nullable(); // for annual leave
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types_tables');
    }
};
