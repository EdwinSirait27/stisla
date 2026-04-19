<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update data lama Holiday → Public Holiday
        DB::table('roster')
            ->where('day_type', 'Holiday')
            ->update(['day_type' => 'Public Holiday']);

        // Ubah enum menggunakan change()
        Schema::table('roster', function (Blueprint $table) {
            $table->enum('day_type', [
                'Work',
                'Off', 
                'Public Holiday',
                'Leave',
                'Cuti Melahirkan'
            ])->default('Work')->change();
        });
    }

    public function down()
    {
        // Rollback data
        DB::table('roster')
            ->where('day_type', 'Public Holiday')
            ->update(['day_type' => 'Holiday']);

        // Rollback enum
        Schema::table('roster', function (Blueprint $table) {
            $table->enum('day_type', [
                'Work',
                'Off',
                'Holiday',
                'Leave'
            ])->default('Work')->change();
        });
    }
};