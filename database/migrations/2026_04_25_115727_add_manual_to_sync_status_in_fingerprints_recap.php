<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fingerprints_recap MODIFY COLUMN sync_status ENUM('Synced', 'Pending', 'Error', 'Manual') NOT NULL DEFAULT 'Synced'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE fingerprints_recap MODIFY COLUMN sync_status ENUM('Synced', 'Pending', 'Error') NOT NULL DEFAULT 'Synced'");
    }
};