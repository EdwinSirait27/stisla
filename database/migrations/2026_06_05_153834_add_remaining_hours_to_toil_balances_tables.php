<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE toil_balances_tables
            ADD COLUMN remaining_hours DECIMAL(8,2)
            AS (GREATEST(0, earned_hours - used_hours)) STORED
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE toil_balances_tables DROP COLUMN remaining_hours");
    }
};