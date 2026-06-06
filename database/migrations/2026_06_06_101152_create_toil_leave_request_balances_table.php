<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toil_leave_request_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('leave_request_id');
            $table->uuid('toil_balance_id');
            $table->decimal('hours_taken', 8, 2);
            $table->timestamps();

            $table->foreign('leave_request_id')
                ->references('id')->on('toil_leave_requests_tables')
                ->onDelete('cascade');

            $table->foreign('toil_balance_id')
                ->references('id')->on('toil_balances_tables')
                ->onDelete('cascade');

            $table->index(['leave_request_id', 'toil_balance_id'], 'tlrb_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toil_leave_request_balances');
    }
};