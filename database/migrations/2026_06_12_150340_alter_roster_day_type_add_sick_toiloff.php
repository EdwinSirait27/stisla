<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE roster MODIFY COLUMN day_type
            ENUM('Work','Off','Public Holiday','Leave','Cuti Melahirkan','TOIL Off','Sick')
            NOT NULL DEFAULT 'Work'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE roster MODIFY COLUMN day_type
            ENUM('Work','Off','Public Holiday','Leave','Cuti Melahirkan')
            NOT NULL DEFAULT 'Work'");
    }
};