<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambah:
     * - status     : 'active' | 'fully_used' | 'expired' | 'cancelled'
     * - paid_at    : kapan saldo Cash masuk payroll (nullable)
     * - paid_period: periode payroll, misal "2026-05" (nullable)
     */
    public function up(): void
    {
        Schema::table('toil_balances_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('toil_balances_tables', 'status')) {
                $table->enum('status', [
                    'active',
                    'fully_used',
                    'expired',
                    'cancelled',
                ])->default('active')->after('expires_at');
            }

            if (!Schema::hasColumn('toil_balances_tables', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('toil_balances_tables', 'paid_period')) {
                $table->string('paid_period', 20)->nullable()->after('paid_at');
            }
        });

        // Tambah index untuk query saldo aktif yang sering dipakai
        Schema::table('toil_balances_tables', function (Blueprint $table) {
            if (!$this->indexExists('toil_balances_tables', 'idx_toil_bal_emp_status_exp')) {
                $table->index(
                    ['employee_id', 'status', 'expires_at'],
                    'idx_toil_bal_emp_status_exp'
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toil_balances_tables', function (Blueprint $table) {
            if ($this->indexExists('toil_balances_tables', 'idx_toil_bal_emp_status_exp')) {
                $table->dropIndex('idx_toil_bal_emp_status_exp');
            }
        });

        Schema::table('toil_balances_tables', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('toil_balances_tables', 'paid_period')) {
                $columns[] = 'paid_period';
            }
            if (Schema::hasColumn('toil_balances_tables', 'paid_at')) {
                $columns[] = 'paid_at';
            }
            if (Schema::hasColumn('toil_balances_tables', 'status')) {
                $columns[] = 'status';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    /**
     * Helper: cek apakah index sudah ada (idempotent).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::connection()->getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?",
            [$database, $table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }
};