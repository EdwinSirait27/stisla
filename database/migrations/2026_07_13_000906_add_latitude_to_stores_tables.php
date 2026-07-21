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
        Schema::table('stores_tables', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
    $table->integer('geofence_radius')->default(100)->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores_tables', function (Blueprint $table) {
            
        });
    }
};