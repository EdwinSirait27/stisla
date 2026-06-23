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
            Schema::table('payrolls', function (Blueprint $table) {
                      $table->decimal('punishment', 15, 2)->default(0);
                      $table->decimal('punishment_so', 15, 2)->default(0);
                      $table->decimal('debt', 15, 2)->default(0);
                      $table->decimal('tax', 15, 2)->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
