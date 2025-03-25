<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN user_type 
            ENUM(
                'Edwin', 
                'Admin', 
                'Head Warehouse',
                'Head Buyer',
                'Buyer',
                'Head Finance',
                'Finance',
                'GM',
                'Manager Store',
                'Supervisor Store',
                'Store Cashier',
                'Warehouse'
                 
            ) 
            NULL
        ");

        // Tambahkan nilai baru ke SET 'role'
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN role 
            SET(
                'Edwin', 
                'Admin', 
                'Head Warehouse',
                'Head Buyer',
                'Buyer',
                'Head Finance',
                'Finance',
                'GM',
                'Manager Store',
                'Supervisor Store',
                'Store Cashier',
                'Warehouse'
            ) 
            NULL
        ");
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
        ALTER TABLE users 
        MODIFY COLUMN user_type 
        ENUM(
            'Edwin', 
            'Admin', 
            'Head Warehouse',
            'Head Buyer',
            'Buyer',
            'Head Finance',
            'Finance',
            'GM',
            'Manager Store',
            'Supervisor Store',
            'Store Cashier'
        ) 
        NULL
    ");

    DB::statement("
        ALTER TABLE users 
        MODIFY COLUMN role 
        SET(
            'Edwin', 
            'Admin', 
            'Head Warehouse',
            'Head Buyer',
            'Buyer',
            'Head Finance',
            'Finance',
            'GM',
            'Manager Store',
            'Supervisor Store',
            'Store Cashier'
        ) 
        NULL
    ");

    }
};
