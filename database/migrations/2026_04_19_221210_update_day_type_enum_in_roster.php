<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    DB::table('roster')
        ->where('day_type', 'Holiday')
        ->update(['day_type' => 'Public Holiday']);

    DB::statement("
        ALTER TABLE roster 
        MODIFY day_type ENUM(
            'Work',
            'Off', 
            'Public Holiday',
            'Leave',
            'Cuti Melahirkan'
        ) DEFAULT 'Work'
    ");
}

public function down()
{
    DB::table('roster')
        ->where('day_type', 'Public Holiday')
        ->update(['day_type' => 'Holiday']);

    DB::statement("
        ALTER TABLE roster 
        MODIFY day_type ENUM(
            'Work',
            'Off',
            'Holiday',
            'Leave'
        ) DEFAULT 'Work'
    ");
}
};